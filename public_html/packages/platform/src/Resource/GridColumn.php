<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

/**
 * Class GridColumn.
 * Describe GridData column.
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class GridColumn
{
    /**
     * @var array <string,string|int>
     */
    private array $attributes = [];

    /**
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->attributes['field'] = $field;
    }

    /**
     * Set attribute.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute(string $name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Set the column flex weight.
     *
     * @param mixed $flex     Example: 1
     * @param mixed $minWidth , Example 200
     *
     * @return $this
     */
    public function flex(mixed $flex = 1, mixed $minWidth = 200): self
    {
        return $this->setAttribute('flex', $flex)->setAttribute('minWidth', $minWidth);
    }

    /**
     * Column should be multiple line with specific lines.
     *
     * @param int $lines
     *
     * @return $this
     */
    public function truncateLines(int $lines = 2): self
    {
        return $this->setAttribute('truncateLines', $lines);
    }

    /**
     * Align column content to left.
     *
     * @return $this
     */
    public function alignLeft(): self
    {
        return $this->setAttribute('align', 'left');
    }

    /**
     * Align column content to right.
     *
     * @return $this
     */
    public function alignRight(): self
    {
        return $this->setAttribute('align', 'right');
    }

    /**
     * Align column content to center.
     *
     * @return $this
     */
    public function alignCenter(): self
    {
        return $this->setAttribute('align', 'center');
    }

    /**
     * Set column width in pixel.
     *
     * @param int $width    Example: 1
     * @param int $minWidth Example: 1
     *
     * @return $this
     */
    public function width(int $width, mixed $minWidth = null): self
    {
        if (!$minWidth) {
            $minWidth = ceil($width * 0.7);
        }

        return $this->setAttribute('width', $width)->setAttribute('minWidth', $minWidth);
    }

    /**
     * Set column header title.
     *
     * @param string $header
     *
     * @return $this
     */
    public function header(string $header): self
    {
        return $this->setAttribute('headerName', $header);
    }

    /**
     * Set align client.
     *
     * @param string $renderCell
     *
     * @return $this
     */
    public function renderAs(string $renderCell): self
    {
        return $this->setAttribute('renderCell', $renderCell);
    }

    /**
     * Set frontend render as number format.
     *
     * @return $this
     */
    public function asNumber(): self
    {
        return $this->setAttribute('renderCell', 'NumberCell');
    }

    /**
     * Add link ref to a field name in the data grid.
     *
     * @param string $field
     *
     * @return $this
     */
    public function linkTo(string $field): self
    {
        return $this->setAttribute('urlField', $field);
    }

    public function target(string $target): static
    {
        return $this->setAttribute('target', $target);
    }

    public function tooltip(string $tooltip): static
    {
        return $this->setAttribute('tooltip', $tooltip);
    }

    /**
     * Add sortable to the grid column.
     *
     * @param bool $sortable
     *
     * @return $this
     */
    public function sortable(bool $sortable = true): self
    {
        return $this->setAttribute('sortable', $sortable);
    }

    /**
     * Set column is editable.
     *
     * @return $this
     */
    public function editable(): self
    {
        return $this->setAttribute('editable', true);
    }

    /**
     * Render as YesNoCell.
     *
     * @return $this
     */
    public function asYesNo(): self
    {
        return $this->setAttribute('renderCell', 'YesNoCell')
            ->alignCenter()
            ->width(120);
    }

    /**
     * Render as a toggle active cell.
     *
     * @return $this
     */
    public function asToggleActive(): self
    {
        return $this->asToggle('toggleActive');
    }

    /**
     * Render as a toggle active cell.
     *
     * @return $this
     */
    public function asToggleDefault(): self
    {
        return $this->asToggle('toggleDefault', true);
    }

    /**
     * Render as a toggle active cell.
     *
     * @return $this
     */
    public function asToggle(string $action, bool $reload = false): self
    {
        return $this->setAttribute('renderCell', 'SwitchActiveCell')
            ->alignCenter()
            ->action($action)
            ->reload($reload)
            ->width(110);
    }

    /**
     * Render as Date Time cell.
     *
     * @param string $format
     * @param bool   $showTime
     *
     * @return $this
     */
    public function asDateTime(string $format = 'medium', bool $showTime = true): self
    {
        return $this->setAttribute('renderCell', 'DateTimeCell')
            ->setAttribute('format', $format)
            ->setAttribute('showTime', $showTime)
            ->width(220);
    }

    /**
     * @param string $format
     *
     * @return $this
     * @link http://numeraljs.com/#format
     */
    public function asNumeral(string $format): self
    {
        return $this->setAttribute('renderCell', 'NumeralCell')
            ->setAttribute('format', $format);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function minWidth(int $minWidth): static
    {
        return $this->setAttribute('minWidth', $minWidth);
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function reload(bool $value = true): static
    {
        return $this->setAttribute('reload', $value);
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function action(string $action): static
    {
        return $this->setAttribute('action', $action);
    }

    public function asId(): static
    {
        return $this->header(__p('core::phrase.id'))
            ->width(80);
    }

    public function asPricing(): static
    {
        return $this->width(200, 120);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function sortableField(string $name): static
    {
        return $this->setAttribute('sortableField', $name);
    }

    public function asIcon(): static
    {
        $this->width(100);

        return $this->setAttribute('renderCell', 'IconCell');
    }

    /**
     * Render as Email cell with a mailto hyperlink.
     *
     * @param string $target
     *
     * @return $this
     */
    public function asEmail(string $target): self
    {
        return $this->setAttribute('renderCell', 'EmailCell')
            ->setAttribute('mailto', $target);
    }

    /**
     * @param array<string, mixed>|null $config
     */
    public function asIconStatus(?array $config = null, ?array $polling = null): static
    {
        $this->width(100)->alignCenter();

        if ($config) {
            $this->setAttribute('iconConfig', $config);
        }

        if (is_array($polling)) {
            $this->polling($polling);
        }

        return $this->setAttribute('renderCell', 'IconStatusCell');
    }

    public function asYesNoIcon(): static
    {
        return $this->asIconStatus([
            'true'  => [
                'icon'    => 'ico-check-circle',
                'color'   => 'success.main',
                'spinner' => false,
                'hidden'  => false,
                'label'   => __p('core::phrase.yes'),
            ],
            'false' => [
                'icon'    => 'ico-minus',
                'color'   => 'text.hint',
                'spinner' => false,
                'hidden'  => false,
                'label'   => __p('core::phrase.no'),
            ],
        ]);
    }

    /**
     * @return $this
     */
    public function asFeaturedUser(): static
    {
        return $this->setAttribute('renderCell', 'UserCell');
    }

    public function tagName(string $tagName): static
    {
        $this->setAttribute('tagName', $tagName);

        return $this;
    }

    public function variant(string $variant): static
    {
        return $this->setAttribute('variant', $variant);
    }

    public function polling(array $config): self
    {
        return $this->setAttribute('pollingConfig', $config);
    }

    public function hint(string $key, ?string $default = null): static
    {
        return $this->setAttribute('tooltip', $key)
            ->setAttribute('tooltipDefault', $default);
    }

    public function asPreviewUrl(): self
    {
        return $this->renderAs('UrlPreviewCell')->aspectRatio();
    }

    public function asHtml(): self
    {
        return $this->renderAs('HtmlCell');
    }

    public function asColoredText(): self
    {
        return $this->renderAs('TextColorCell');
    }

    public function aspectRatio(string $aspectRatio = 'auto'): self
    {
        return $this->setAttribute('aspectRatio', $aspectRatio);
    }

    public function fieldDisabled(string $field): self
    {
        return $this->setAttribute('fieldDisabled', $field);
    }
}
