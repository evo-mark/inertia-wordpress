<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude([
        'development',
    ])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PSR12' => true,
        '@PHP83Migration' => true,
        'no_unused_imports' => true
    ])
    ->setFinder($finder);
