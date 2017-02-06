<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zendframework for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Exception;

/**
 * View helper plugin to fetch asset from resource map.
 */
class Asset extends AbstractHelper
{
    /**
     * @var array
     */
    protected $resourceMap = [];

    /**
     * @param string $asset
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke($asset)
    {
        if (! array_key_exists($asset, $this->resourceMap)) {
            throw new Exception\InvalidArgumentException('Asset is not defined.');
        }

        return $this->resourceMap[$asset];
    }

    /**
     * @param array $resourceMap
     * @return $this
     */
    public function setResourceMap(array $resourceMap)
    {
        $this->resourceMap = $resourceMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getResourceMap()
    {
        return $this->resourceMap;
    }
}
