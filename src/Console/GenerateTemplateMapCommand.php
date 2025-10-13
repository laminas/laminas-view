<?php

declare(strict_types=1);

namespace Laminas\View\Console;

use Laminas\View\Exception\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function assert;
use function file_put_contents;
use function is_string;
use function trim;

final class GenerateTemplateMapCommand extends Command
{
    public const DEFAULT_NAME = 'generate-template-map';

    public function __construct()
    {
        parent::__construct(self::DEFAULT_NAME);
    }

    protected function configure(): void
    {
        $this->setDescription(<<<'TXT'
            Scans a directory of templates and creates a configuration file to use as a template map for laminas-view
            TXT);

        $this->addArgument(
            'templates',
            InputArgument::REQUIRED,
            'The path to the template directory to scan for files',
        );

        $this->addArgument(
            'config',
            InputArgument::REQUIRED,
            <<<'TXT'
                The path to the configuration file you wish to create.
                The parent directory must exist and the file name must end with ".php".
                Example: './config/autoload/my-module-templates.global.php'
                TXT,
        );

        $this->addArgument(
            'suffix',
            InputArgument::OPTIONAL,
            <<<'TXT'
                The filename suffix of your template files.
                Defaults to `phtml`
                TXT,
            'phtml',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style  = new SymfonyStyle($input, $output);
        $scan   = $input->getArgument('templates');
        $target = $input->getArgument('config');
        $suffix = $input->getArgument('suffix');

        assert(is_string($scan) && is_string($target) && is_string($suffix));

        try {
            $generator = new TemplateMapGenerator($scan, $target, trim($suffix, '.'));
        } catch (InvalidArgumentException $e) {
            $style->error($e->getMessage());

            return self::INVALID;
        }

        $write = file_put_contents($target, $generator());
        if ($write === false) {
            $style->error('Failed to write map to ' . $target);

            return self::FAILURE;
        }

        $style->success('Wrote template map to ' . $target);

        return self::SUCCESS;
    }
}
