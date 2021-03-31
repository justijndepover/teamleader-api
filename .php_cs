<?php

require __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->notPath([
        'vendor',
    ])
    ->in([
        __DIR__ . '/src',
        // __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php');

return \Justijndepover\PHPCheck\Rules::all($finder);
