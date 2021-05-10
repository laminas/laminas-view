<?php

if (class_exists(PHPUnit_Framework_Error_Deprecated::class)
    && ! class_exists(PHPUnit\Framework\Error\Deprecated::class)) {
    class_alias(PHPUnit_Framework_Error_Deprecated::class, PHPUnit\Framework\Error\Deprecated::class);
}
