<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\I18n\Translator\Loader\FileLoaderInterface;
use Laminas\I18n\Translator\TextDomain;

class ArrayTranslator implements FileLoaderInterface
{
    /** @var string[]|null */
    public $translations;

    /**
     * @param string $locale
     * @param string $filename
     */
    public function load($locale, $filename): TextDomain
    {
        return new TextDomain($this->translations);
    }
}
