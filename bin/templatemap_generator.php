#!/usr/bin/env php
<?php
/**
 * @link      http://github.com/zendframework/zend-view for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

// Setup/verify autoloading
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // Local install
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(getcwd() . '/vendor/autoload.php')) {
    // Root project is current working directory
    require getcwd() . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    // Relative to composer install
    require __DIR__ . '/../../../autoload.php';
} else {
    fwrite(STDERR, "Unable to setup autoloading; aborting\n");
    exit(2);
}

$help = <<< EOH
Generate template maps.

Usage:

templatemap_generator.php [-h|--help] templatepath files...

--help|-h                    Print this usage message.
templatepath                 Path to templates relative to current working
                             path; used to identify what to strip from
                             template names.
files...                     List of files to include in the template
                             map, relative to the current working path.

The script assumes that paths included in the template map are relative
to the current working directory.

The script will output a PHP script that will return the template map
on successful completion. You may save this to a file using standard
piping operators; use ">" to write to/ovewrite a file, ">>" to append
to a file (which may have unexpected and/or intended results; you will
need to edit the file after generation to ensure it contains valid
PHP).

We recommend you then include the generated file within your module
configuration:

  'template_map' => include __DIR__ . '/template_map.config.php',

Examples:

  # Create a template_map.config.php file in the Application module's
  # config directory, relative to the view directory, and only containing
  # .phtml files; overwrite any existing files:
  $ cd module/Application/config/
  $ ../../../vendor/bin/templatemap_generator.php ../view ../view/**/*.phtml > template_map.config.php
EOH;

// Called without arguments
if ($argc < 2) {
    echo $help;
    exit(2);
}

// Requested help
if (in_array($argv[1], ['-h', '--help'], true)) {
    echo $help, "\n";
    exit(0);
}

// Not enough arguments
if ($argc < 3) {
    echo $help;
    exit(2);
}

$basePath = $argv[1];
$files    = array_slice($argv, 2);
$map      = [];
$realPath = realpath($basePath);

$entries = array_map(function ($file) use ($basePath, $realPath) {
    $file     = str_replace('\\', '/', $file);

    $template = (0 === strpos($file, $realPath))
        ? substr($file, strlen($realPath))
        : $file;

    $template = (0 === strpos($template, $basePath))
        ? substr($template, strlen($basePath))
        : $template;

    $template = preg_match('#(?P<template>.*?)\.[a-z0-9]+$#i', $template, $matches)
        ? $matches['template']
        : $template;

    $template = preg_replace('#^\.*/#', '', $template);
    $file     = sprintf('__DIR__ . \'/%s\'', $file);

    return sprintf("    '%s' => %s,\n", $template, $file);
}, $files);

echo '<' . "?php\nreturn [\n"
    . implode('', $entries)
    . '];';

exit(0);
