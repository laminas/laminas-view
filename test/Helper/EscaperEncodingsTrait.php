<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

trait EscaperEncodingsTrait
{
    /** @var string[] */
    private $supportedEncodings = [
        'iso-8859-1',   'iso8859-1',    'iso-8859-5',   'iso8859-5',
        'iso-8859-15',  'iso8859-15',   'utf-8',        'cp866',
        'ibm866',       '866',          'cp1251',       'windows-1251',
        'win-1251',     '1251',         'cp1252',       'windows-1252',
        '1252',         'koi8-r',       'koi8-ru',      'koi8r',
        'big5',         '950',          'gb2312',       '936',
        'big5-hkscs',   'shift_jis',    'sjis',         'sjis-win',
        'cp932',        '932',          'euc-jp',       'eucjp',
        'eucjp-win',    'macroman',
    ];

    /** @return iterable<string, array<int, string>> */
    public function supportedEncodingsProvider(): iterable
    {
        foreach ($this->supportedEncodings as $encoding) {
            yield $encoding => [$encoding];
        }
    }
}
