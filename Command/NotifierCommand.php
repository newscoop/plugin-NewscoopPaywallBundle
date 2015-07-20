<?php

/**
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
    private $input;
    private $output;

    protected function configure()
    {
        $this
            ->setName('paywall:notifier:expiring')
            ->setDescription('Sends email notifications for expiring subscriptions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->input = $input;
            $this->output = $output;
            $now = new \DateTime();
            $this->runProcessing($now, 7, Emails::NOTIFY_LEVEL_ONE);
            $this->runProcessing($now, 3, Emails::NOTIFY_LEVEL_TWO);
        } catch (\Exception $e) {
            $this->output->writeln('<error>Error occured: '.$e->getMessage().'</error>');

            return false;
        }
    }

    private function runProcessing($now, $daysBefore, $level)
    {
        $notificationService = $this->getContainer()->getService('newscoop_paywall.notifications_service');
        $subscriptionsCount = $notificationService->getExpiringSubscriptionsCount(
            $now,
            $level,
            $daysBefore
        );

        if ($subscriptionsCount !== 0) {
            $notificationService->processExpiringSubscriptions(
                $now,
                $level,
                $subscriptionsCount,
                $daysBefore
            );

            if ($this->input->getOption('verbose')) {
                $this->output->writeln('<info>'.$subscriptionsCount.' notifications sent... (which expire in '.$daysBefore.' days)</info>');
            }
        } else {
            if ($this->input->getOption('verbose')) {
                $this->output->writeln('<info>There are no subscriptions expiring within '.$daysBefore.' day(s).<info>');
            }
        }
    }
}
