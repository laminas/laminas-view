<?php

declare(strict_types=1);

namespace Laminas\View\Exception;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Throwable;

use function sprintf;

final class RenderingFailedException extends RuntimeException
{
    /** @param non-empty-string $filePath */
    public static function becauseTheTemplateFileCouldNotBeIncluded(string $filePath): self
    {
        return new self(sprintf(
            'Failed to render template because the template file could not be included: "%s"',
            $filePath,
        ));
    }

    public static function becauseATemplateWasNotSpecified(): self
    {
        return new self(
            'A template must be specified during rendering, '
            . 'either as an argument or as a property of the view model',
        );
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $filePath
     */
    public static function becauseOfAccessToAnUndeclaredVariable(string $name, string $filePath): self
    {
        return new self(sprintf(
            'Access to an undeclared variable "%s" in the template "%s"',
            $name,
            $filePath,
        ));
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $filePath
     */
    public static function becauseMemberVariablesCannotBeMutated(string $name, string $filePath): self
    {
        return new self(sprintf(
            'Attempt to mutate the variable "%s" in the template "%s"',
            $name,
            $filePath,
        ));
    }

    public static function becauseOfAnException(string $template, Throwable $error): self
    {
        return new self(sprintf(
            'An exception occurred during render of "%s" with the message: %s"',
            $template,
            $error->getMessage(),
        ), 0, $error);
    }

    /** @param non-empty-string $pluginName */
    public static function becauseOfAPluginException(string $pluginName, Throwable $error): self
    {
        return new self(sprintf(
            'An exception occurred during execution of the plugin "%s". Message: %s',
            $pluginName,
            $error->getMessage(),
        ), 0, $error);
    }

    /** @param non-empty-string $template */
    public static function becauseARenderLoopHasBeenDetected(string $template): self
    {
        return new self(sprintf(
            'A cyclic rendering dependency has been detected during render of the template "%s"',
            $template,
        ));
    }

    public static function becauseTheTemplateCannotBeResolvedToAFile(string $templateName): self
    {
        return new self(sprintf(
            'Unable to render template "%s"; resolver could not resolve to a file',
            $templateName,
        ));
    }

    public static function becauseOfAnUnknownPlugin(
        string $alias,
        string $templatePath,
        ServiceNotFoundException $previous,
    ): self {
        return new self(sprintf(
            'Access to an unknown view helper alias "%s" from the template "%s"',
            $alias,
            $templatePath,
        ), 0, $previous);
    }

    public static function becauseOfAmbiguousArgumentsToPhpRenderer(): self
    {
        return new self(
            'Passing both view model and view variables to render is ambiguous. '
            . 'Either provide just the model, or, a template name and the variables with '
            . 'which to create the model.'
        );
    }
}
