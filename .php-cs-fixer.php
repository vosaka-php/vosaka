<?php

use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true
    ])
    ->setFinder($finder);