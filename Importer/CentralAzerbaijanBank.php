<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Importer;

/**
 * Central Azerbaijan Bank Importer class.
 */
class CentralAzerbaijanBank extends AbstractImporter
{
    private $url = 'http://www.cbar.az/currencies/';

    /**
     * {@inheritdoc}
     */
    public function configure(array $options = array())
    {
        if (!isset($options['base_currency'])) {
            throw new \InvalidArgumentException('"base_currency" must be set in order to use CentralAzerbaijanBank.');
        }

        $this->baseCurrency = $options['base_currency'];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $managedCurrencies = array())
    {
        $now = new \DateTime('now');
        $this->url = $this->url.$now->format('d.m.Y').'.xml';
        $xml = @simplexml_load_file($this->url);
        if ($xml instanceof \SimpleXMLElement) {
            // base currency: AZN
            $this->updateOrCreate($managedCurrencies, $this->baseCurrency, 1.00);

            $data = $xml->xpath('//ValCurs/*[1]');
            // divides 1.00 by current exchange rate to get the reverse rate,
            // because CBAR provides only inverse rate
            foreach ($data[0]->children() as $child) {
                $this->updateOrCreate(
                    $managedCurrencies,
                    (string) $child->attributes()->Code,
                    1.00 / (float) $child->children()->Value
                );
            }

            $this->manager->flush();
        }
    }
}
