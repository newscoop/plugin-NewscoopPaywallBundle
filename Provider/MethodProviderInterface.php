<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Provider;

/**
 * Payment Method Provider interface.
 */
interface MethodProviderInterface
{
    public function getActiveMethod();

    public function getDefaultMethod();

    public function getEnabledMethods();
}
