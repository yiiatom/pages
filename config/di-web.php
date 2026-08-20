<?php

declare(strict_types=1);

use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\IdMessageReader;
use Yiisoft\Translator\IntlMessageFormatter;
use Yiisoft\Translator\Message\Php\MessageSource;
use Yiisoft\Translator\SimpleMessageFormatter;

return [
    'atom.pages.categorySource' => [
        'definition' => static function () use ($params): CategorySource {
            $reader = class_exists(MessageSource::class)
                ? new MessageSource(\dirname(__DIR__) . '/messages')
                : new IdMessageReader(); // @codeCoverageIgnore

            $formatter = \extension_loaded('intl')
                ? new IntlMessageFormatter()
                : new SimpleMessageFormatter(); // @codeCoverageIgnore

            return new CategorySource('atom-users', $reader, $formatter);
        },
        'tags' => ['translation.categorySource'],
    ],
];
