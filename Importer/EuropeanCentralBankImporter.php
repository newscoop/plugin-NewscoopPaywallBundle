<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Importer;

/**
 * European Central Bank Importer class.
 */
class EuropeanCentralBankImporter extends AbstractImporter
{
    private $url = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';

    /**
     * {@inheritdoc}
     */
    public function configure(array $options = array())
    {
        if (!isset($options['base_currency'])) {
            throw new \InvalidArgumentException('"base_currency" must be set in order to use EuropeanCentralBankImporter.');
        }

        $this->baseCurrency = $options['base_currency'];
    }

    /**
     * {@inheritdoc}
     */
    public function import(array $managedCurrencies = array())
    {
        $xml = @simplexml_load_file($this->url);
        if ($xml instanceof \SimpleXMLElement) {
            // base currency: euro
            $this->updateOrCreate($managedCurrencies, $this->baseCurrency, 1.00);

            $data = $xml->xpath('//gesmes:Envelope/*[3]/*');
            foreach ($data[0]->children() as $child) {
                $this->updateOrCreate($managedCurrencies, (string) $child->attributes()->currency, (float) $child->attributes()->rate);
            }

            $this->manager->flush();
        }
    }
}
