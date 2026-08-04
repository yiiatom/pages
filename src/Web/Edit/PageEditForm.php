<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Edit;

use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Uuid;

final class PageEditForm extends FormModel
{
    private ?string $path = null;

    private iterable $parents = [];

    #[Label('Parent')]
    #[Uuid(skipOnEmpty: true)]
    public ?string $parentUuid = null;

    #[Label('Title')]
    #[Required]
    #[Length(max: 255, skipOnEmpty: true)]
    public ?string $title = null;

    #[Label('Slug')]
    #[Required]
    #[Length(max: 255, skipOnEmpty: true)]
    #[Regex(
        pattern: '/^[a-z0-9-]+$/',
        skipOnEmpty: true,
        skipOnError: true,
    )]
    public ?string $slug = null;

    #[Label('Content')]
    #[Required]
    public ?string $content = null;

    #[Label('Status')]
    #[Required]
    public string $status = PageStatus::DRAFT->value;

    public function withPath(string $path): self
    {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    public function withParents(iterable $parents): self
    {
        $new = clone $this;
        $new->parents = $parents;

        return $new;
    }

    public function getParentUuidOptions(): array
    {
        $options = ['' => '— No Parent —'];

        /** @var Page $page */
        foreach ($this->parents as $page) {
            if ($page->getPath() === $this->path) {
                continue;
            }

            if (str_starts_with($page->getPath(), $this->path . '/')) {
                continue;
            }

            $indent = str_repeat('    ', $page->getDepth());
            $prefix = $page->getDepth() > 0 ? '↳ ' : '';
            $options[$page->getUuid()] = $indent . $prefix . $page->getTitle();
        }

        return $options;
    }

    public function getStatusOptions(): array
    {
        return [
            PageStatus::DRAFT->value => PageStatus::DRAFT->getLabel(),
            PageStatus::PUBLISHED->value => PageStatus::PUBLISHED->getLabel(),
        ];
    }
}
