<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use Illuminate\Support\Arr;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\TraitAttributeBag;

/**
 * Class MenuItemConfig.
 */
class MenuItem
{
    use TraitAttributeBag;

    /**
     * MenuItemConfig constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->attributes['name'] = $name;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function style(string $style): static
    {
        return $this->setAttribute('style', $style);
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function label(string $label): static
    {
        return $this->setAttribute('label', $label);
    }

    /**
     * Set value to batch edit.
     * @return $this
     * @link Constants::ACTION_BATCH_EDIT
     */
    public function asBatchEdit(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_BATCH_EDIT);
    }

    /**
     * Set value to ACTION_BATCH_ACTIVE.
     * @return $this
     * @link Constants::ACTION_BATCH_ACTIVE
     */
    public function asBatchActive(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_BATCH_ACTIVE);
    }

    /**
     * Set value to ACTION_BATCH_INACTIVE.
     * @return $this
     * @link Constants::ACTION_BATCH_INACTIVE
     */
    public function asBatchInactive(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_BATCH_INACTIVE);
    }

    /**
     * Set value to ACTION_ROW_EDIT.
     * @return $this
     * @link Constants::ACTION_ROW_EDIT
     */
    public function asEditRow(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_ROW_EDIT);
    }

    /**
     * Set value to ACTION_ROW_DELETE.
     * @return $this
     * @link Constants::ACTION_ROW_DELETE
     */
    public function asDeleteRow(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_ROW_DELETE);
    }

    /**
     * Set value to ACTION_ROW_ADD.
     * @return $this
     * @link Constants::ACTION_ROW_ADD
     */
    public function asAddRow(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_ROW_ADD);
    }

    public function asDownload(): static
    {
        return $this->setAttribute('value', MetaFoxForm::ACTION_ROW_DOWNLOAD);
    }

    /**
     * Set icon attribute.
     *
     * @param string $iconName
     *
     * @return $this
     */
    public function icon(string $iconName): static
    {
        return $this->setAttribute('icon', $iconName);
    }

    /**
     * Set attribute value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function value(string $value): static
    {
        return $this->setAttribute('value', $value);
    }

    /**
     * Set params array.
     *
     * @param array<string,mixed> $params
     *
     * @return $this
     */
    public function params(array $params): static
    {
        return $this->setAttribute('params', $params);
    }

    /**
     * Add show when rule array.
     *
     * @param  array $params
     * @return $this
     */
    public function showWhen(array $params): static
    {
        return $this->setAttribute('showWhen', $params);
    }

    /**
     * Set value of "enabledWhen" attribute.
     *
     * @param  array $params
     * @return $this
     */
    public function enableWhen(array $params): static
    {
        return $this->setAttribute('enabledWhen', $params);
    }

    /**
     * Set disabled attribute.
     *
     * @param bool $disabled
     *
     * @return $this
     */
    public function disabled(bool $disabled): static
    {
        return $this->setAttribute('disabled', $disabled);
    }

    /**
     * @param mixed $method
     * @param mixed $args
     *
     * @return MenuItem
     */
    public function __call($method, $args)
    {
        return $this->setAttribute($method, $args[0] ?? null);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Set params.action attribute.
     *
     * @param  string $action
     * @return $this
     */
    public function action(string $action): static
    {
        if (!isset($this->attributes['params'])) {
            $this->attributes['params'] = [];
        }

        Arr::set($this->attributes, 'params.action', $action);

        return $this;
    }

    public function confirm(mixed $confirm): static
    {
        if (true === $confirm) {
            $confirm = [
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('core::phrase.are_you_absolutely_sure_this_operation_cannot_be_undone'),
            ];
        }

        Arr::set($this->attributes, 'params.confirm', $confirm);

        return $this;
    }

    public function alert(mixed $alert): static
    {
        Arr::set($this->attributes, 'params.alert', $alert);

        return $this;
    }

    public function reload(mixed $value = true): static
    {
        Arr::set($this->attributes, 'params.reload', $value);

        return $this;
    }

    public function as(string $name): static
    {
        return $this->setAttribute('as', $name);
    }
}
