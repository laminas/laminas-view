<?php

declare(strict_types=1);

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
     */
    public function testSetErrorLevelImplementsFluentInterface(): void
    {
        $model  = new ConsoleModel();
        $actual = $model->setErrorLevel(0);
        $this->assertSame($model, $actual);
    }
}
