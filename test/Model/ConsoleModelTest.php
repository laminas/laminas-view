<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\View\Model;

use Laminas\View\Model\ConsoleModel;
use Laminas\View\Model\ModelInterface;
use PHPUnit\Framework\TestCase;

class ConsoleModelTest extends TestCase
{
    public function testImplementsModelInterface(): void
    {
        $model = new ConsoleModel();
        $this->assertInstanceOf(ModelInterface::class, $model);
    }

    /**
     * @see https://github.com/zendframework/zend-view/issues/152
     *
     * @return void
     */
    public function testSetErrorLevelImplementsFluentInterface(): void
    {
        $model = new ConsoleModel();
        $actual = $model->setErrorLevel(0);
        $this->assertSame($model, $actual);
    }
}
