<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Import currencies echange rates from diffrent services.
 */
class CurrencyExchangeRateImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('paywall:currency:import')
            ->setDescription('Import currencies exchange rates. Default service is European Central Bank')
            ->addArgument('service', InputArgument::OPTIONAL, 'Name of the service', 'ecb')
            ->addArgument('currency', InputArgument::OPTIONAL, 'Default currency', 'EUR')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $service = $container->get('newscoop_paywall.currency_importer.'.$input->getArgument('service'));
        $provider = $container->get('newscoop_paywall.currency_provider');

        if ($input->getOption('verbose')) {
            $output->writeln('<info>Importing exchange rates started!</info>');
        }

        $service->setBaseCurrency($input->getArgument('currency'));
        $service->import($provider->getAvailableCurrencies());

        if ($input->getOption('verbose')) {
            $output->writeln('<info>Exchange rates imported successfully!</info>');
        }
    }
}
