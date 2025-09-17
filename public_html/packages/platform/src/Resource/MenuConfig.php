<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Resource;

use Illuminate\Support\Collection;

/**
 * Class MenuConfig.
 *
 * Describe menu config
 */
class MenuConfig
{
    /**
     * @var Collection<MenuItem>
     */
    protected Collection $items;

    /**
     * @var string|null
     */
    protected ?string $variant = null;

    /**
     * MenuConfig constructor.
     */
    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * Add more menu items.
     *
     * @param string $name
     *
     * @return MenuItem
     */
    public function addItem(string $name): MenuItem
    {
        $item = new MenuItem($name);

        $this->items->add($item);

        return $item;
    }

    /**
     * @param string|null $variant
     *
     * @return MenuConfig
     */
    public function setVariant(?string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function asButton(): self
    {
        $this->variant = 'IconLabel';

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'variant' => $this->variant,
            'items'   => $this->items->map(function (MenuItem $item) {
                return $item->toArray();
            }),
        ];
    }
}
