<?php

declare(strict_types=1);
use Pest\Preset;

if (class_exists(Preset::class)) {
    arch('php preset')->preset()->php();
}

arch('it will not use unsafe PHP functions')
    ->expect([
        'md5',
        'sha1',
        'uniqid',
        'rand',
        'mt_rand',
        'tempnam',
        'str_shuffle',
        'shuffle',
        'array_rand',
        'eval',
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'create_function',
        'unserialize',
        'extract',
        'mb_parse_str',
        'dl',
        'assert',
    ])
    ->each->not->toBeUsed();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('Ahmaruff\HtmxTable')
    ->toUseStrictTypes();
