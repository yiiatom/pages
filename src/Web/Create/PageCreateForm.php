<?php

declare(strict_types=1);

namespace Atom\Pages\Web\Create;

use Atom\Helper\FormTranslatorTrait;
use Atom\Pages\Entity\Page;
use Atom\Pages\Entity\PageStatus;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\LabelsProviderInterface;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Uuid;

final class PageCreateForm extends FormModel implements LabelsProviderInterface
{
    use FormTranslatorTrait;

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

    public function withParents(iterable $parents): self
    {
        $new = clone $this;
        $new->parents = $parents;

        return $new;
    }

    public function getParentUuidOptions(): array
    {
        $t = $this->getTranslator();

        $options = ['' => $t->translate('[No Parent]')];

        /** @var Page $page */
        foreach ($this->parents as $page) {
            $indent = str_repeat('    ', $page->getDepth());
            $prefix = $page->getDepth() > 0 ? '↳ ' : '';
            $options[$page->getUuid()] = $indent . $prefix . $page->getTitle();
        }

        return $options;
    }

    public function getStatusOptions(): array
    {
        $t = $this->getTranslator();

        return [
            PageStatus::DRAFT->value => $t->translate(PageStatus::DRAFT->getLabel()),
            PageStatus::PUBLISHED->value => $t->translate(PageStatus::PUBLISHED->getLabel()),
        ];
    }
}
