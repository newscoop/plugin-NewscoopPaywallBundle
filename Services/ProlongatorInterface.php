<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Newscoop\PaywallBundle\Entity\ProlongableItemInterface;

/**
 * Prolongator interface.
 */
interface ProlongatorInterface
{
    /**
     * Creates prolongation requests. Adds the prolongation object
     * to the database for given order item. By default set to
     * not approved, which means, admin needs to approve the prolongation.
     *
     * @param ProlongableItemInterface $item
     * @param Duration                 $period
     */
    public function createRequest(ProlongableItemInterface $item, $period);

    public function prolong(ProlongableItemInterface $item, $period);
}
