<?php

namespace MetaFox\Saved\Support\Browse\Scopes\Saved;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    protected int $userId;
    protected int $savedListId = 0;
    protected string $itemType = '';

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return ViewScope
     */
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSavedListId(): int
    {
        return $this->savedListId;
    }

    /**
     * @param int $savedListId
     *
     * @return ViewScope
     */
    public function setSavedListId(int $savedListId): self
    {
        $this->savedListId = $savedListId;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemType(): string
    {
        return $this->itemType;
    }

    /**
     * @param string $itemType
     *
     * @return ViewScope
     */
    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        switch ($this->getSavedListId()) {
            case 0:
                $builder->where($this->alias($table, 'user_id'), $this->getUserId());
                break;
            default:
                $builder->join('saved_list_data AS sld', function (JoinClause $join) use ($table) {
                    $join->on('sld.saved_id', '=', $this->alias($table, 'id'));
                    $join->where('sld.list_id', $this->getSavedListId());
                });
                break;
        }

        if ($this->getItemType() != '' && $this->getItemType() != Browse::VIEW_ALL) {
            $builder->where($this->alias($table, 'item_type'), $this->getItemType());
        }
    }
}
