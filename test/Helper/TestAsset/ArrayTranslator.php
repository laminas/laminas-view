<?php

namespace LaminasTest\View\Helper\TestAsset;

use Laminas\I18n\Translator;

class ArrayTranslator implements Translator\Loader\FileLoaderInterface
{
    public $translations;

    public function load($filename, $locale)
    {
        $textDomain = new Translator\TextDomain($this->translations);
        return $textDomain;
    }
}
