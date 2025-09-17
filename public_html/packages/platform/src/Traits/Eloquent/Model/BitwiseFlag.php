<?php

namespace MetaFox\Platform\Traits\Eloquent\Model;

use MetaFox\Platform\Contracts\IsBitwiseFlagInterface;

/**
 * Trait BitwiseFlag.
 * @package MetaFox\Platform\Traits\Eloquent\Model
 * @mixin IsBitwiseFlagInterface
 */
trait BitwiseFlag
{
    /**
     * @param int $flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFlag(int $flag): bool
    {
        $name = $this->getFlagName();

        return ($this->$name & $flag) == $flag;
    }

    /**
     * @param int  $flag
     * @param bool $value
     *
     * @return $this
     */
    public function setFlag(int $flag, bool $value)
    {
        $name = $this->getFlagName();

        if ($value) {
            $this->$name |= $flag;

            return $this;
        }

        $this->$name &= ~$flag;

        return $this;
    }
}
