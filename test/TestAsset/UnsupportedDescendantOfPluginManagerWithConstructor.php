<?php

declare(strict_types=1);

namespace LaminasTest\View\TestAsset;

use Laminas\View\HelperPluginManager;

/**
 * This test asset can be removed in 3.0 when the plugin manager can be made final
 *
 * @deprecated Remove this in 3.0
 *
 * @psalm-suppress all
 */
final class UnsupportedDescendantOfPluginManagerWithConstructor extends HelperPluginManager
{
    public const EXPECTED_HELPER_OUTPUT = 'Goats are friends';

    /** @inheritDoc */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        parent::__construct($configOrContainerInstance, $v3config);
        $helper = static function (): string {
            return self::EXPECTED_HELPER_OUTPUT;
        };

        $this->factories['Laminas\Test\FactoryBackedHelper'] = static fn (): callable => $helper;
        $this->aliases['aliasForTestHelper']                 = 'Laminas\Test\FactoryBackedHelper';
    }
}
