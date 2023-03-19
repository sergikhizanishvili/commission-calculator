<?php

require 'vendor/autoload.php';

use Sergi\TaskPhpRefactoring\CommissionCalculator;

echo implode("\n", (new CommissionCalculator(file_get_contents($argv[1])))->commissions()) . "\n";