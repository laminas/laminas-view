<?php

declare(strict_types=1);

namespace LaminasTest\View\StaticAnalysis;

use Laminas\View\TemplateInterface;
use Override;
use PhpParser;
use Psalm\Context;
use Psalm\Internal\Analyzer\ClassAnalyzer;
use Psalm\Internal\Analyzer\FileAnalyzer;
use Psalm\Internal\Analyzer\MethodAnalyzer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Internal\Provider\NodeDataProvider;
use Psalm\Node\Stmt\VirtualClass;
use Psalm\Node\Stmt\VirtualClassMethod;
use Psalm\Storage\MethodStorage;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;

use function preg_replace;

/**
 * phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName
 *
 * @psalm-suppress UnusedClass, InternalClass, InternalMethod, InternalProperty
 * @codeCoverageIgnore
 */
final class TemplateAnalyzer extends FileAnalyzer
{
    #[Override]
    public function analyze(
        Context|null $file_context = null,
        Context|null $global_context = null,
    ): void {
        $codebase                        = $this->project_analyzer->getCodebase();
        $stmts                           = $codebase->getStatementsForFile($this->file_path);
        $context                         = new Context();
        $context->self                   = TemplateInterface::class;
        $context->vars_in_scope['$this'] = new Union([
            new TNamedObject(TemplateInterface::class),
        ]);

        $this->checkTemplate($context, $stmts);
    }

    /** @param list<PhpParser\Node\Stmt> $stmts */
    private function checkTemplate(Context $context, array $stmts): void
    {
        $pseudoMethodStmts = [];

        foreach ($stmts as $stmt) {
            if ($stmt instanceof PhpParser\Node\Stmt\Use_) {
                $this->visitUse($stmt);

                continue;
            }

            $pseudoMethodStmts[] = $stmt;
        }

        $pseudoClassName  = (string) preg_replace('/[^a-zA-Z0-9_]+/', '_', $this->file_name);
        $pseudoMethodName = '__internalPseudoRenderForPsalm';
        $method           = new VirtualClassMethod($pseudoMethodName, ['stmts' => []]);
        $class            = new VirtualClass($pseudoClassName, [
            'implements' => [
                new PhpParser\Node\Name(TemplateInterface::class),
            ],
        ]);

        $classAnalyzer  = new ClassAnalyzer($class, $this, TemplateInterface::class);
        $methodAnalyzer = new MethodAnalyzer($method, $classAnalyzer, new MethodStorage());

        /**
         * All templates have `@var TemplateInterface $this` annotated
         */
        $methodAnalyzer->addSuppressedIssue('UnnecessaryVarAnnotation');
        $methodAnalyzer->addSuppressedIssue('NoInterfaceProperties');

        $statementAnalyzer = new StatementsAnalyzer(
            $methodAnalyzer,
            new NodeDataProvider(),
        );

        $statementAnalyzer->analyze($pseudoMethodStmts, $context);
    }
}
