<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Exception\InvalidArgumentException;

use function sprintf;
use function stripos;
use function stristr;

final class DoctypeV3
{
    public const DEFAULT_DOCTYPE = self::HTML5;

    public const XHTML11             = 'XHTML11';
    public const XHTML1_STRICT       = 'XHTML1_STRICT';
    public const XHTML1_TRANSITIONAL = 'XHTML1_TRANSITIONAL';
    public const XHTML1_FRAMESET     = 'XHTML1_FRAMESET';
    public const XHTML1_RDFA         = 'XHTML1_RDFA';
    public const XHTML1_RDFA11       = 'XHTML1_RDFA11';
    public const XHTML_BASIC1        = 'XHTML_BASIC1';
    public const XHTML5              = 'XHTML5';
    public const HTML4_STRICT        = 'HTML4_STRICT';
    public const HTML4_LOOSE         = 'HTML4_LOOSE';
    public const HTML4_FRAMESET      = 'HTML4_FRAMESET';
    public const HTML5               = 'HTML5';

    /** @var array<string, non-empty-string> */
    private static $docTypeDefinitions = [
        self::XHTML11             => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" '
                                     . '"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        self::XHTML1_STRICT       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '
                                     . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        self::XHTML1_TRANSITIONAL => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '
                                     . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        self::XHTML1_FRAMESET     => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" '
                                     . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        self::XHTML1_RDFA         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" '
                                     . '"http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
        self::XHTML1_RDFA11       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" '
                                     . '"http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">',
        self::XHTML_BASIC1        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" '
                                     . '"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">',
        self::XHTML5              => '<!DOCTYPE html>',
        self::HTML4_STRICT        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" '
                                     . '"http://www.w3.org/TR/html4/strict.dtd">',
        self::HTML4_LOOSE         => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" '
                                     . '"http://www.w3.org/TR/html4/loose.dtd">',
        self::HTML4_FRAMESET      => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" '
                                     . '"http://www.w3.org/TR/html4/frameset.dtd">',
        self::HTML5               => '<!DOCTYPE html>',
    ];

    private string $doctype;

    public function __construct(string $configuredDocTypeId = self::DEFAULT_DOCTYPE)
    {
        $this->doctype = $configuredDocTypeId;
    }

    public function __invoke(): self
    {
        return $this;
    }

    public function __toString(): string
    {
        return $this->doctypeDeclaration();
    }

    /** @return non-empty-string */
    public function doctypeDeclaration(?string $doctypeId = null): string
    {
        $id = $doctypeId ?: $this->doctype;
        if (! isset(self::$docTypeDefinitions[$id])) {
            throw new InvalidArgumentException(sprintf(
                'There is not a doctype declaration known with the id "%s"',
                $id
            ));
        }

        return self::$docTypeDefinitions[$id];
    }

    /**
     * Is doctype XHTML?
     */
    public function isXhtml(?string $doctypeId = null): bool
    {
        return (bool) stristr($this->doctypeDeclaration($doctypeId), 'xhtml');
    }

    /**
     * Is doctype HTML5?
     */
    public function isHtml5(?string $doctypeId = null): bool
    {
        return $this->doctypeDeclaration($doctypeId) === $this->doctypeDeclaration(self::HTML5);
    }

    /**
     * Is doctype RDFa?
     */
    public function isRdfa(?string $doctypeId = null): bool
    {
        return $this->isHtml5($doctypeId)
            ||
            stripos($this->doctypeDeclaration($doctypeId), 'rdfa') !== false;
    }
}
