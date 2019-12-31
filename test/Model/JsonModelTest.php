<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Model;

use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @category   Laminas
 * @package    Laminas_View
 * @subpackage UnitTest
 */
class JsonModelTest extends TestCase
{
    public function testAllowsEmptyConstructor()
    {
        $model = new JsonModel();
        $this->assertInstanceOf('Laminas\View\Variables', $model->getVariables());
        $this->assertEquals(array(), $model->getOptions());
    }

    public function testCanSerializeVariablesToJson()
    {
        $array = array('foo' => 'bar');
        $model = new JsonModel($array);
        $this->assertEquals($array, $model->getVariables());
        $this->assertEquals(Json::encode($array), $model->serialize());
    }


    public function testCanSerializeWithJsonpCallback()
    {
        $array = array('foo' => 'bar');
        $model = new JsonModel($array);
        $model->setJsonpCallback('callback');
        $this->assertEquals('callback(' . Json::encode($array) . ');', $model->serialize());
    }
}
