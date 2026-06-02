<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector;

return RectorConfig::configure()
    ->withPhpSets(php81: true)
    ->withPaths([
        __DIR__ . '/../../src',
        __DIR__ . '/../../test',
    ])
    ->withSkipPath(__DIR__ . '/../../test/TestAsset/TranslatorStubFactory.php')
    ->withPreparedSets(
        typeDeclarations: true,
    )->withSkip([
        /** This rector will narrow interfaces to concrete implementations in method return types */
        NarrowObjectReturnTypeRector::class,
    ]);
