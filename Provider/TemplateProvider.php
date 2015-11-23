<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Provider;

use Newscoop\PaywallBundle\Entity\Repository\SubscriptionRepository;

/**
 * Subscription Template Provider.
 */
class TemplateProvider implements TemplateProviderInterface
{
    /** @var SubscriptionRepository */
    protected $repository;

    /**
     * Construct.
     *
     * @param SubscriptionRepository $repository Repository
     */
    public function __construct(SubscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableTemplates($type, $locale)
    {
        return $this->repository
            ->findTemplates($type, $locale)
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getOneTemplate($id, $locale = null)
    {
        return $this->repository
            ->findActiveOneBy($id, $locale);
    }
}
