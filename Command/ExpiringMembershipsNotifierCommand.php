<?php
/**
 * @package   Newscoop\PaywallBundle
 * @author    Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Notifies about expiring memberships
 */
class ExpiringMembershipsNotifierCommand extends ContainerAwareCommand
{
    /**
     */
    protected function configure()
    {
        $this
        ->setName('membership:expired')
        ->setDescription('Notifies about expiring memberships');
    }

    /**
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        try {
            $em = $this->getContainer()->getService('em');
            $membershipService = $this->getContainer()->getService('newscoop_paywall.membership');
            $now = new \DateTime();

            $qb = $em->getRepository('Newscoop\PaywallBundle\Entity\UserSubscription')
                ->createQueryBuilder('s');

            $qbRows = clone($qb);

            //expire_at - 7 days < now
            //send notification
            $qb
                ->select('count(s)')
                ->where("DATE_SUB(s.expire_at, 7, 'DAY') < :now")
                ->orWhere("DATE_SUB(s.expire_at, 3, 'DAY') < :now")
                ->andWhere('s.active = :status')
                ->setParameters(array(
                    'status' => 'Y',
                    'now' => $now
                ))
                ->orderBy('s.created_at', 'desc');

            $subscriptionsCount = (int) $qb->getQuery()->getSingleScalarResult();

            $batch = 100;
            $steps = ($subscriptionsCount > $batch) ? ceil($subscriptionsCount / $batch) : 1;
            for ($i = 0; $i < $steps; $i++) {

                $offset = $i * $batch;

                $qbRows
                    ->where("DATE_SUB(s.expire_at, 7, 'DAY') < :now")
                    ->orWhere("DATE_SUB(s.expire_at, 3, 'DAY') < :now")
                    ->andWhere('s.active = :status')
                    ->setParameters(array(
                        'status' => 'Y',
                        'now' => $now
                    ))
                    ->orderBy('s.created_at', 'desc')
                    ->setFirstResult($offset)
                    ->setMaxResults($batch);

                $expiringSubscriptions = $qbRows->getQuery()->getResult();

                foreach ($expiringSubscriptions as $key => $subscription) {
                    $membershipService->expiringSubscriptionNotifyEmail($subscription);
                }
            }

            if ($input->getOption('verbose')) {
                $output->writeln('<info>Finished... '.$subscriptionsCount.' reminders sent...</info>');
            }

        } catch (\Exception $e) {
            if ($input->getOption('verbose')) {
                $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');
            }

            return false;
        }
    }
}
