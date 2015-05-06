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
use Newscoop\PaywallBundle\Notifications\Emails;

/**
 * Console command responsible for sending email
 * notifications for expiring subscriptions.
 */
class NotifierCommand extends ContainerAwareCommand
{
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
            $notificationService = $this->getContainer()->getService('newscoop_paywall.notifications_service');
            $now = new \DateTime();
            $subscriptionsCount = $notificationService->getExpiringSubscriptionsCount(
                $now,
                Emails::NOTIFY_LEVEL_ONE,
                7
            );

            if ($subscriptionsCount !== 0) {
                $notificationService->processExpiringSubscriptions(
                    $now,
                    Emails::NOTIFY_LEVEL_ONE,
                    $subscriptionsCount,
                    7
                );

                if ($input->getOption('verbose')) {
                    $output->writeln('<info>'.$subscriptionsCount.' notifications sent...(which expire in 7 days)</info>');
                }
            } else {
                if ($input->getOption('verbose')) {
                    $output->writeln('<info>There are no subscriptions expiring within 7 day(s).<info>');
                }
            }

            $subscriptionsCount = $notificationService->getExpiringSubscriptionsCount(
                $now,
                Emails::NOTIFY_LEVEL_TWO,
                3
            );

            if ($subscriptionsCount !== 0) {
                $notificationService->processExpiringSubscriptions(
                    $now,
                    Emails::NOTIFY_LEVEL_TWO,
                    $subscriptionsCount,
                    3
                );

                if ($input->getOption('verbose')) {
                    $output->writeln('<info>'.$subscriptionsCount.' notifications sent... (which expire in 3 days)</info>');
                }
            } else {
                if ($input->getOption('verbose')) {
                    $output->writeln('<info>There are no subscriptions expiring within 3 day(s).<info>');
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error occured: '.$e->getMessage().'</error>');

            return false;
        }
    }
}
