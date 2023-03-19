<?php

namespace Sergi\TaskPhpRefactoring;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class for calculating commission
 * based on txt file passed along.
 *
 */
class CommissionService
{

    /**
     * Guzzle HTTP client object.
     *
     * @var Client
     */
    protected $client;

    /**
     * API endpoint for resolving BIN number.
     *
     * @var string
     */
    protected $binApiUri = 'https://lookup.binlist.net';

    /**
     * API endpoint for retreiving exchange rates.
     *
     * @var string
     */
    protected $exchangeRateApiUri = 'https://api.apilayer.com/exchangerates_data/latest';

    /**
     * Access token for exchange rate API.
     *
     * @var string
     */
    private $exchangeRateApiToken;

    /**
     * EU countries.
     *
     * @var array
     */
    protected $countriesEU = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PO',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    /**
     * Set up class and all its methods and properties.
     *
     * @return void
     */
    public function __construct()
    {

        $this->client = new Client();

        // This could be done via .env library when implementing on production.
        $this->exchangeRateApiToken = 'gKkWBiuparRy402W2KMIGWwWdBi3DxkM';
    }

    /**
     * Get exchange rates via API.
     *
     * @return object Exchange rates.
     */
    public function getExchangeRates() : object
    {
        try {
            // Get response from the API.
            $response = $this->client->get($this->exchangeRateApiUri, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'apikey'       => $this->exchangeRateApiToken,
                ]
            ])->getBody()->getContents();

            $response = json_decode($response);
            
            // Check if we got valid response.
            if (empty($response) || !isset($response->rates)) {
                die('Error! Unable to retreive exchange rate via API.');
            }
            
            return $response->rates;
        } catch (BadResponseException $e) {
            die('Error! Unable to retreive exchange rate via API: ' . $e->getMessage());
        }
    }


    /**
     * Check if card was issued in EU or not.
     *
     * @param int $bin Card BIN number (First numbers of the card).
     *
     * @return bool TRUE if card was issued in EU or FALSE.
     */
    public function isEU(int $bin) : bool
    {
        try {
            // Get reponse from the API.
            $response = $this->client->get($this->binApiUri . '/' . $bin, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ])->getBody()->getContents();

            $response = json_decode($response);
            
            // Check if we got valid response.
            if (empty($response) || !isset($response->country->alpha2)) {
                die('Error! Cannot resolve BIN via API.');
            }

            // Check if its EU country.
            if (in_array($response->country->alpha2, $this->countriesEU, true)) {
                return true;
            }
            
            return false;
        } catch (BadResponseException $e) {
            die('Error! Unable to retreive bin results via API: ' . $e->getMessage());
        }
    }
}
