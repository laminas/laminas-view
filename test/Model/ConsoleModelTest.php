<?php

namespace ZendTest\View\Model;

use PHPUnit\Framework\TestCase;
use Zend\View\Model\ConsoleModel;

class ConsoleModelTest extends TestCase
{
    public function testImplementsModelInterface()
    {
        $model = new ConsoleModel();
        $this->assertInstanceOf('Zend\View\Model\ModelInterface', $model);
    }

    /**
     * @group zend-view-152
     * @see https://github.com/zendframework/zend-view/issues/152
     */
    public function testSetErrorLevelIsReturningThis()
    {
        $model = new ConsoleModel();
        $actual = $model->setErrorLevel(0);
        $this->assertSame($model, $actual);
    }
}
