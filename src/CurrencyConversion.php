<?php


namespace CurrencyConversion;


/**
 * Class CurrencyConversion
 * @property $error Boolean
 * @property $conversionDict array
 */
class CurrencyConversion
{
    private $error = false;
    private $conversionDict = [];
    private static $instance = null;

    private function __construct()
    {
        $xml = simpleXML_load_file("https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
        if ($xml === FALSE) {
            $this->error = true;
            return;
        }
        foreach ($xml->Cube->Cube->Cube as $rate) {
            $this->conversionDict[(string)$rate["currency"]] = (float)$rate['rate'];
        }
    }

    public static function getInstance(): CurrencyConversion
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return static::$instance;
    }

    public function hasError(): bool
    {
        return $this->error;
    }

    public function getRate(string $to,string $from = 'EUR'): float
    {


        if ($to === $from) {
            return 1;
        }
        if ($from === 'EUR') {
            throw_if(!isset($this->conversionDict[$to]), new \RuntimeException("Currency $to not supported"));
            return $this->conversionDict[$to];
        }
        if ($to === 'EUR') {
            throw_if(!isset($this->conversionDict[$from]), new \RuntimeException("Currency $from not supported"));

            return 1/$this->conversionDict[$from];
        }

        return $this->getRate($from) * $this->getRate('EUR', $to);
    }
    public function convert(float $amount,string $to,string $from): float
    {
        return $this->getRate($to, $from)*$amount;
    }

}
