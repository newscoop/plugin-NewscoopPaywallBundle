<?php

/**
 * @author Rafał Muszyński <rmuszynski1@gmail.com>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trials entity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_paywall_trials")
 */
class Trial
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="IdUser", referencedColumnName="Id")
     *
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * @ORM\Column(type="boolean", name="had_trial")
     *
     * @var bool
     */
    protected $hadTrial;

    /**
     * @ORM\Column(type="datetime", name="trial_finish")
     *
     * @var datetime
     */
    protected $finishTrial;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\PaywallBundle\Entity\Subscription")
     * @ORM\JoinColumn(name="trial_for_subscription", referencedColumnName="id")
     *
     * @var Newscoop\PaywallBundle\Entity\Subscriptions
     */
    protected $subscription;

    /**
     * @ORM\Column(type="datetime", name="trial_created_at")
     *
     * @var string
     */
    protected $created_at;

    /**
     * @ORM\Column(type="boolean", name="is_active")
     *
     * @var bool
     */
    protected $is_active;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setIsActive(true);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user.
     *
     * @return Newscoop\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user.
     *
     * @param Newscoop\Entity\User $user
     *
     * @return Newscoop\Entity\User
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $user;
    }

    /**
     * Get had trial.
     *
     * @return bool
     */
    public function getHadTrial()
    {
        return $this->hadTrial;
    }

    /**
     * Set had trial.
     *
     * @param bool $hadTrial
     *
     * @return bool
     */
    public function setHadTrial($hadTrial)
    {
        $this->hadTrial = $hadTrial;

        return $this;
    }

    /**
     * Get trial finish date.
     *
     * @return datetime
     */
    public function getFinishTrial()
    {
        return $this->finishTrial;
    }

    /**
     * Set finish Trial.
     *
     * @param datetime $finishTrial
     *
     * @return datetime
     */
    public function setFinishTrial($finishTrial)
    {
        $this->finishTrial = $finishTrial;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set status.
     *
     * @param bool $is_active
     *
     * @return bool
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Get create date.
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set create date.
     *
     * @param datetime $created_at
     *
     * @return datetime
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get subscription.
     *
     * @return Newscoop\PaywallBundle\Entity\Subscriptions
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Get subscription.
     *
     * @param Newscoop\PaywallBundle\Entity\Subscription $subscription
     *
     * @return Newscoop\PaywallBundle\Entity\Subscriptions
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;

        return $subscription;
    }
}
