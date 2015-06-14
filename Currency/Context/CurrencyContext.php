<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Currency\Context;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Currency Context class.
 */
class CurrencyContext implements CurrencyContextInterface
{
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->session->get(self::CURRENCY_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        return $this->session->set(self::CURRENCY_KEY, strtoupper($currency));
    }
}
