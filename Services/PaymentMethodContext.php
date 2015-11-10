<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Payment Context class.
 */
class PaymentMethodContext implements PaymentMethodInterface
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
    public function getMethod()
    {
        return $this->session->get(self::METHOD_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method = null)
    {
        return $this->session->set(self::METHOD_KEY, $method);
    }
}
