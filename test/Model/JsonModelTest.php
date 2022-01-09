<?php

declare(strict_types=1);

namespace LaminasTest\View\Model;

use Laminas\View\Exception\DomainException;
use Laminas\View\Model\JsonModel;
use Laminas\View\Variables;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

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
        $this->assertJsonStringEqualsJsonString(json_encode($array, JSON_THROW_ON_ERROR), $model->serialize());
    }

    public function testCanSerializeWithJsonpCallback(): void
    {
        $array = ['foo' => 'bar'];
        $model = new JsonModel($array);
        $model->setJsonpCallback('callback');
        $expect = sprintf(
            'callback(%s);',
            json_encode($array, JSON_THROW_ON_ERROR)
        );
        $this->assertEquals($expect, $model->serialize());
    }

    public function testPrettyPrint(): void
    {
        $array  = [
            'simple'              => 'simple test string',
            'stringwithjsonchars' => '\"[1,2]',
            'complex'             => [
                'foo' => 'bar',
                'far' => 'boo',
            ],
        ];
        $model  = new JsonModel($array, ['prettyPrint' => true]);
        $expect = json_encode($array, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->assertEquals($expect, $model->serialize());
    }

    public function testThatAnExceptionIsThrownIfItIsNotPossibleToEncodeThePayload(): void
    {
        $malformedUtf8 = [
            'string' => "\x92",
        ];
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Failed to encode Json');
        (new JsonModel($malformedUtf8))->serialize();
    }
}
