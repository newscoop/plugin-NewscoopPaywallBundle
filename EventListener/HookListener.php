<?php
/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginHooksEvent;
use Newscoop\PaywallBundle\Permissions;

/**
 * Hook listener.
 */
class HookListener
{
    private $templating;
    private $entityManager;
    private $templatesProvider;
    private $pluginsService;
    private $userService;

    public function __construct(
        $templating,
        $entityManager,
        $templatesProvider,
        $pluginsService,
        $userService
    ) {
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->templatesProvider = $templatesProvider;
        $this->pluginsService = $pluginsService;
        $this->userService = $userService;
    }

    public function sidebar(PluginHooksEvent $event)
    {
        $user = $this->userService->getCurrentUser();
        if (!$this->pluginsService->isEnabled(LifecycleSubscriber::PLUGIN_NAME) || !$user->hasPermission(Permissions::SIDEBAR)) {
            return;
        }

        $article = $event->getArgument('article');
        $user = $this->userService->getCurrentUser();
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
                'isPublic' => $article->isPublic(),
                'hasPermission' => (!$article->userCanModify($user) || !$user->hasPermission('Publish')),
            )
        );

        $event->addHookResponse($response);
    }
}
