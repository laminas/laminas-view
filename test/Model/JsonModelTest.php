<?php

namespace LaminasTest\View\Model;

use Laminas\Json\Json;
use Laminas\View\Model\JsonModel;
use Laminas\View\Variables;
use PHPUnit\Framework\TestCase;

class JsonModelTest extends TestCase
{
    public function testAllowsEmptyConstructor(): void
    {
        $model = new JsonModel();
        $this->assertInstanceOf(Variables::class, $model->getVariables());
        $this->assertEquals([], $model->getOptions());
    }

    public function testCanSerializeVariablesToJson(): void
    {
        $array = ['foo' => 'bar'];
        $model = new JsonModel($array);
        $this->assertEquals($array, $model->getVariables());
        $this->assertEquals(Json::encode($array), $model->serialize());
    }

    public function testCanSerializeWithJsonpCallback(): void
    {
        $array = ['foo' => 'bar'];
        $model = new JsonModel($array);
        $model->setJsonpCallback('callback');
        $this->assertEquals('callback(' . Json::encode($array) . ');', $model->serialize());
    }

    public function testPrettyPrint(): void
    {
        $array = [
            'simple' => 'simple test string',
            'stringwithjsonchars' => '\"[1,2]',
            'complex' => [
                'foo' => 'bar',
                'far' => 'boo'
            ]
        ];
        $model = new JsonModel($array, ['prettyPrint' => true]);
        $this->assertEquals(Json::encode($array, false, ['prettyPrint' => true]), $model->serialize());
    }
}
