<?php

namespace ZendTest\View;

use PHPUnit_Framework_TestCase as TestCase;
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
        return new HelperPluginManager(new ServiceManager());
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
