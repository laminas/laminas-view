<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Helper\Json as JsonHelper;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class JsonTest extends TestCase
{
    private JsonHelper $helper;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->helper = new JsonHelper();
    }

    public function testJsonHelperReturnsJsonEncodedString(): void
    {
        $input  = [
            'dory' => 'blue',
            'nemo' => 'orange',
        ];
        $expect = json_encode($input, JSON_THROW_ON_ERROR);
        self::assertJsonStringEqualsJsonString($expect, ($this->helper)->__invoke($input));
    }

    public function testTheHelperWillPrettyPrintWhenRequired(): void
    {
        $input  = [
            'dory' => 'blue',
            'nemo' => 'orange',
        ];
        $expect = json_encode($input, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        self::assertSame($expect, ($this->helper)->__invoke($input, ['prettyPrint' => true]));
    }
}
