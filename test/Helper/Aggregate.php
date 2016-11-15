<?php

namespace ZendTest\View\Helper;

class Aggregate
{
    public $vars = [
        'foo' => 'bar',
        'bar' => 'baz'
    ];

    public function toArray()
    {
        return $this->vars;
    }
}
