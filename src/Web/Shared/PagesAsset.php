<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Shared;

use Yiisoft\Assets\AssetBundle;

final class PagesAsset extends AssetBundle
{
    public ?string $basePath = '@assets';
    public ?string $baseUrl = '@assetsUrl';
    public ?string $sourcePath = '@atom-pages/assets';

    public array $css = [
        'quill/dist/quill.snow.css',
        'pages.css',
    ];

    public array $js = [
        'quill/dist/quill.js',
        'pages.js',
    ];
}
