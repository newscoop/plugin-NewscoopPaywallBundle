<?php
/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginHooksEvent;

/**
 * Hook listener.
 */
class HookListener
{
    private $templating;
    private $entityManager;
    private $templatesProvider;

    public function __construct($templating, $entityManager, $templatesProvider)
    {
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->templatesProvider = $templatesProvider;
    }

    public function sidebar(PluginHooksEvent $event)
    {
        $article = $event->getArgument('article');
        $specification = $this->entityManager
            ->getRepository('Newscoop\PaywallBundle\Entity\SubscriptionSpecification')
            ->findSpecification(
                $article->getArticleNumber(),
                $article->getPublicationId()
            );

        $language = $this->entityManager
            ->getReference('Newscoop\Entity\Language', $article->getLanguageId());
        $templates = $this->templatesProvider->getAvailableTemplates('article', $language->getCode());

        $response = $this->templating->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            array(
                'templates' => $templates,
                'specification' => $specification ?: null,
                'status' => $specification ? true : false,
                'articleNumber' => $article->getArticleNumber(),
                'articleLanguage' => $article->getLanguageId(),
            )
        );

        $event->addHookResponse($response);
    }
}
