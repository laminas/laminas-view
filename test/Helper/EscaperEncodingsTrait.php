<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use function assert;

trait EscaperEncodingsTrait
{
    /** @var list<string> */
    private static array $supportedEncodings = [
        'iso-8859-1',
        'iso8859-1',
        'iso-8859-5',
        'iso8859-5',
        'iso-8859-15',
        'iso8859-15',
        'utf-8',
        'cp866',
        'ibm866',
        '866',
        'cp1251',
        'windows-1251',
        'win-1251',
        '1251',
        'cp1252',
        'windows-1252',
        '1252',
        'koi8-r',
        'koi8-ru',
        'koi8r',
        'big5',
        '950',
        'gb2312',
        '936',
        'big5-hkscs',
        'shift_jis',
        'sjis',
        'sjis-win',
        'cp932',
        '932',
        'euc-jp',
        'eucjp',
        'eucjp-win',
        'macroman',
    ];

    /** @return iterable<string, array<int, non-empty-string>> */
    public static function supportedEncodingsProvider(): iterable
    {
        foreach (self::$supportedEncodings as $encoding) {
            assert(! empty($encoding));
            yield $encoding => [$encoding];
        }
    }
}
