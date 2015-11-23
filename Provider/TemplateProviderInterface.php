<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Provider;

use Newscoop\PaywallBundle\Entity\PriceableInterface;

/**
 * Template Provider Interface.
 */
interface TemplateProviderInterface
{
    /**
     * Gets all the available subscriptions marked as a template.
     *
     * @param string $type   Subscription type (e.g. article, issue etc.)
     * @param string $locale Current locale
     *
     * @return PriceableInterface[]
     */
    public function getAvailableTemplates($type, $locale);

    public function getOneTemplate($id, $locale = null);
}
