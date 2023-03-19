<?php

namespace Sergi\TaskPhpRefactoring;

use Sergi\TaskPhpRefactoring\CommissionService;

/**
 * Class for calculating commission
 * based on txt file passed along.
 *
 */
class CommissionCalculator
{
    /**
     * Commission API service.
     *
     * @var CommissionService
     */
    public $service;

    /**
     * Transactions array from the file.
     *
     * @var array
     */
    public $transactions;

    /**
     * Exchange rates from the API.
     *
     * @var object
     */
    public $exchangeRates;

    /**
     * Commission rate for EU issued cards.
     *
     * @var float
     */
    protected $euCommission = 0.01;

    /**
     * Commission rate for non EU issued cards.
     *
     * @var float
     */
    protected $commission = 0.02;

    /**
     * Set up class and all its methods and properties.
     *
     * @param string $fileContent Content of the passed file.
     * @param CommissionService $commission service object
     *
     * @return void
     */
    public function __construct(string $fileContent, CommissionService $service = new CommissionService())
    {
        $this->service = $service;
        $this->exchangeRates = $this->service->getExchangeRates();
        $this->transactions = explode("\n", $fileContent);
    }

    /**
     * Commissions output.
     *
     * @return array Commissions for each transaction.
     */
    public function commissions() : array
    {

        if (empty($this->transactions) || !is_array($this->transactions)) {
            die('Invalid or empty file was passed.');
        }

        $commissions = [];

        foreach ($this->transactions as $row) {
            $transaction = json_decode($row);

            if (empty($transaction)) {
                break;
            }

            $commissions[] = $this->calculate($transaction);
        }

        return $commissions;
    }

    /**
     * Calculate commissio for particulat transaction.
     *
     * @param object $transaction Transaction object.
     *
     * @return float Commission for the transaction.
     */
    public function calculate(object $transaction) : float
    {

        $isEu  = $this->service->isEu($transaction->bin);
        $inEur = $this->convertInEur($transaction->currency, $transaction->amount);
        $rate  = $isEu ? $this->euCommission : $this->commission;

        return round($inEur * $rate, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Convert transaction amount in EUR via API.
     *
     * @param string $currency Transaction currency.
     * @param float  $amount Transaction amount.
     *
     * @return float Amount converted to EUR.
     */
    public function convertInEur(string $currency, float $amount) : float
    {

        if ('EUR' === $currency) {
            return (float) $amount;
        }

        // Check if we got currency in rates.
        if (!isset($this->exchangeRates->$currency) || !is_float($this->exchangeRates->$currency)) {
            die('Error! Invalid currency or no data for this currency from the API.');
        }
        
        return $amount / $this->exchangeRates->$currency;
    }
}
