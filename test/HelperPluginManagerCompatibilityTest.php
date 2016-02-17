<?php

namespace ZendTest\View;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\Config;
use Zend\View\Exception\InvalidHelperException;
use Zend\View\Helper\HelperInterface;
use Zend\View\HelperPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class PluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        $config = new Config(
            [
                'services' => [
                    'config' => [],
                ],
                'factories' => [
                    'ControllerPluginManager' => function ($services, $name, $options) {
                        return new PluginManager($services, [
                            'invokables' => [
                                'flashmessenger' => 'Zend\Mvc\Controller\Plugin\FlashMessenger',
                            ],
                        ]);
                    },
                ],
            ]
        );
        $manager = new ServiceManager();
        $config->configureServiceManager($manager);
        $helperManager = new HelperPluginManager($manager);

        return $helperManager;
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidHelperException::class;
    }

    protected function getInstanceOf()
    {
        return HelperInterface::class;
    }
}
