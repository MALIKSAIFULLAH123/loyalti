<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Traits\Eloquent\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTotalItem;
use MetaFox\Platform\Contracts\HasTotalView;

/**
 * Trait HasIncrementAmountColumns.
 * @mixin HasAmounts
 */
trait HasAmountsTrait
{
    /**
     * @param string $column
     * @param int    $amount
     *
     * @return int
     */
    public function incrementAmount(string $column, int $amount = 1): int
    {
        $hasTimestamps = $this->timestamps == true;

        if ($hasTimestamps) {
            $this->timestamps = false;
        }

        $current = (int) Arr::get($this->attributes, $column);

        if ($current < 0) {
            $this->handleNegativeNumber($column);
        }

        $result = $this->incrementQuietly($column, $amount);

        if ($hasTimestamps) {
            $this->timestamps = true;
        }

        app('events')->dispatch("core.{$column}_updated", [$this, 'increment']);

        return $result;
    }

    /**
     * @param string $column
     * @param int    $amount
     *
     * @return int
     */
    public function decrementAmount(string $column, int $amount = 1): int
    {
        $hasTimestamps = $this->timestamps == true;

        if ($hasTimestamps) {
            $this->timestamps = false;
        }

        $current = (int) Arr::get($this->attributes, $column);

        if ($current < 0) {
            $this->handleNegativeNumber($column);
            $current = 0;
        }

        if ($current < $amount) {
            if ($current > 0) {
                $this->handleNegativeNumber($column);
            }

            return 0;
        }

        $this->handleDecrement($column, $amount);

        if ($hasTimestamps) {
            $this->timestamps = true;
        }

        $this->refresh();

        app('events')->dispatch("core.{$column}_updated", [$this, 'decrement']);

        return $this->$column;
    }

    public function incrementTotalView(): void
    {
        if ($this instanceof HasTotalView) {
            $this->incrementAmount('total_view');
        }
    }

    public function incrementTotalItem(): void
    {
        if ($this instanceof HasTotalItem) {
            $this->incrementAmount('total_item');
        }
    }

    public function decrementTotalItem(): void
    {
        if ($this instanceof HasTotalItem) {
            $this->decrementAmount('total_item');
        }
    }

    protected function handleNegativeNumber(string $column): void
    {
        $this->fill([$column => 0]);
        $this->saveQuietly();
    }

    private function handleDecrement(string $column, int $amount = 1): void
    {
        if ($this instanceof Entity) {
            $this->newQuery()
                ->where($this->getKeyName(), $this->entityId())
                ->update([
                    $column => DB::raw("GREATEST($column - $amount, 0)"),
                ]);

            return;
        }

        // fail-over
        $this->decrementQuietly($column, $amount);
    }
}
