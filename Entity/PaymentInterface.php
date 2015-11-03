<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Entity;

interface PaymentInterface
{
    const STATE_NEW = 'new';
    const STATE_COMPLETED = 'completed';
    const STATE_FAILED = 'failed';
    const STATE_CANCELLED = 'cancelled';
    const STATE_UNKNOWN = 'unknown';

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @param null|string $method
     */
    public function setMethod($method = null);

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     */
    public function setState($state);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string
     */
    public function setCurrency($currency);

    /**
     * @return int
     */
    public function getAmount();

    /**
     * @param int $amount
     */
    public function setAmount($amount);

    /**
     * @param array|\Traversable $details
     */
    public function setDetails($details);

    /**
     * @return array
     */
    public function getDetails();
}
