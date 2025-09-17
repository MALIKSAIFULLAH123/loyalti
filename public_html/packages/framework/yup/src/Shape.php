<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Yup;

/**
 * @category framework
 */
interface Shape
{
    /**
     * @param string|null $label
     *
     * @return $this
     */
    public function label(?string $label): self;

    /**
     * @return array<string,mixed>|null
     */
    public function toArray(): ?array;
}
