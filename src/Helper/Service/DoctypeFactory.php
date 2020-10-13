<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Exception;
use Laminas\View\Helper\Doctype;

class DoctypeFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param null|array         $options
     * @return Doctype
     * @throws Exception\RuntimeException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $helper = new Doctype();

        if (! $container->has('config')) {
            return $helper;
        }
        $config = $container->get('config');
        if (isset($config['view_helper_config']['doctype'])) {
            $helper->setDoctype($config['view_helper_config']['doctype']);
        }

        return $helper;
    }
}
