<?php

/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Newscoop\PaywallBundle\Events\PaywallEvents;
use Newscoop\EventDispatcher\Events\GenericEvent;

/**
 * Console command responsible for sending email
 * notifications for expiring subscriptions.
 */
class NotifierCommand extends ContainerAwareCommand
{
    private $em;
    private $dispatcher;

    protected function configure()
    {
        $this
            ->setName('paywall:notifier:expiring')
            ->setDescription('Sends email notifications for expiring subscriptions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO spool email notifications
        try {
            $this->em = $this->getContainer()->getService('em');
            $now = new \DateTime();
            $subscriptionsCount = $this->getExpiringSubscriptionsCount($now);

            if ($subscriptionsCount == 0) {
                $output->writeln('<info>There are no expiring subscriptions.<info>');

                return;
            }
            $this->dispatcher = $this->getContainer()->getService('event_dispatcher');
            $this->processExpiringSubscriptions($subscriptionsCount, $now);

            if ($input->getOption('verbose')) {
                $output->writeln('<info>Finished... '.$subscriptionsCount.' notifications sent...</info>');
            }
        } catch (\Exception $e) {
            if ($input->getOption('verbose')) {
                $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');
            }

            return false;
        }
    }

    private function getExpiringSubscriptionsCount($now)
    {
        $qb = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
                ->createQueryBuilder('s');
        $qb
                ->select('count(s)')
                ->where("DATE_SUB(s.expire_at, 7, 'DAY') < :now")
                ->orWhere("DATE_SUB(s.expire_at, 3, 'DAY') < :now")
                ->andWhere('s.active = :status')
                ->andWhere('s.notifySent = :notifySent')
                ->setParameters(array(
                    'status' => 'Y',
                    'now' => $now,
                    'notifySent' => false,
                ))
                ->orderBy('s.created_at', 'desc');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function processExpiringSubscriptions($subscriptionsCount, $now)
    {
        $qb = $this->em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
                ->createQueryBuilder('s');

        $batch = 100;
        $steps = ($subscriptionsCount > $batch) ? ceil($subscriptionsCount / $batch) : 1;
        for ($i = 0; $i < $steps; $i++) {
            $offset = $i * $batch;

            $qb
                ->where("DATE_SUB(s.expire_at, 7, 'DAY') < :now")
                ->orWhere("DATE_SUB(s.expire_at, 3, 'DAY') < :now")
                ->andWhere('s.active = :status')
                ->andWhere('s.notifySent = :notifySent')
                ->setParameters(array(
                    'status' => 'Y',
                     'now' => $now,
                     'notifySent' => false,
                ))
                ->orderBy('s.created_at', 'desc')
                ->setFirstResult($offset)
                ->setMaxResults($batch);

            $expiringSubscriptions = $qb->getQuery()->getResult();
            foreach ($expiringSubscriptions as $key => $subscription) {
                $this->dispatcher->dispatch(
                    PaywallEvents::SUBSCRIPTION_EXPIRATION,
                    new GenericEvent($subscription)
                );
            }
        }
    }
}
