<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Newscoop\PaywallBundle\Entity\Subscription;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Newscoop\PaywallBundle\Entity\SubscriptionSpecification;
use Newscoop\Entity\Article;
use Doctrine\Common\Collections\ArrayCollection;
use Newscoop\PaywallBundle\Permissions;

class HookController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/sidebar/{articleNumber}/{articleLanguage}/{allowed}", options={"expose"=true})
     * @Method("POST")
     */
    public function sidebarAction(Request $request, $articleNumber, $articleLanguage, $allowed = false)
    {
        $entityManager = $this->get('em');
        $templateId = $request->request->get('paywallTemplateSubscriptionId');
        $templatesProvider = $this->get('newscoop_paywall.subscription_template_provider');
        $article = $this->findOneOr404($articleNumber, $articleLanguage);
        $specification = $entityManager
            ->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
            ->findSpecification(
                $article->getNumber(),
                $article->getPublicationId()
            );

        $templates = $templatesProvider->getAvailableTemplates('article', $article->getLanguageCode());
        $subscription = $templatesProvider->getOneTemplate($templateId);
        if (!$subscription) {
            return $this->returnResponse(array(
                'status' => false,
            ));
        }

        if ($specification) {
            $specification->getSubscription()->setIsActive(false);
            $specification->setIsActive(false);
        } else {
            // make switch unchecked when subscription exists for given article
            $article->setPublic('N');
        }

        $specification = $this->buildSubscription($subscription, $article);

        return $this->returnResponse(array(
            'specification' => $specification,
            'templates' => $templates,
            'articleNumber' => $article->getNumber(),
            'articleLanguage' => $article->getLanguageId(),
            'isPublic' => $article->getPublic() === 'Y' ? true : false,
            'hasPermission' => $allowed,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/sidebar/{articleNumber}/{articleLanguage}", options={"expose"=true})
     * @Method("PATCH")
     */
    public function unmarkAction($articleNumber, $articleLanguage)
    {
        $this->hasPermission(Permissions::SIDEBAR);
        $entityManager = $this->get('em');
        $article = $this->findOneOr404($articleNumber, $articleLanguage);
        $specification = $entityManager
            ->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
            ->findSpecification(
                $article->getNumber(),
                $article->getPublicationId()
            );

        if (!$specification) {
            return new JsonResponse(array(
                'status' => false,
            ));
        }

        $specification->getSubscription()->setIsActive(false);
        $specification->setIsActive(false);
        $entityManager->flush();

        return new JsonResponse(array(
            'status' => true,
        ));
    }

    private function findOneOr404($articleNumber, $articleLanguage)
    {
        $entityManager = $this->get('em');
        $article = $entityManager->getRepository('Newscoop\Entity\Article')->findOneBy(array(
            'number' => $articleNumber,
            'language' => $articleLanguage,
        ));

        if (!$article) {
            throw new NotFoundHttpException('The article does not exist.');
        }

        return $article;
    }

    private function buildSubscription(Subscription $subscription, Article $article)
    {
        $entityManager = $this->get('em');
        $subscription = clone $subscription;
        $subscription->setCreatedAt(new \DateTime());
        $subscription->setName($subscription->getName().'-'.$article->getNumber());
        $subscription->setIsTemplate(false);
        $ranges = new ArrayCollection();
        foreach ($subscription->getRanges() as $value) {
            $value = clone $value;
            $value->setSubscription($subscription);
            $ranges->add($value);
        }

        $subscription->setRanges($ranges);
        $entityManager->persist($subscription);
        $specification = new SubscriptionSpecification();
        $specification->setSubscription($subscription);
        $specification->setPublication($article->getPublicationId());
        $specification->setIssue($article->getIssue()->getNumber());
        $specification->setSection($article->getSection()->getNumber());
        $specification->setArticle($article->getNumber());

        $entityManager->persist($specification);
        $entityManager->flush();

        return $specification;
    }

    private function returnResponse(array $data)
    {
        return $this->container->get('templating')->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            $data
        );
    }
}
