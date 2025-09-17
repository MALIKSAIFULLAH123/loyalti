<?php

namespace MetaFox\Saved\Support\Browse\Scopes\Saved;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Saved\Models\SavedListItemView;

class OpenStatusScope extends BaseScope
{
    protected string $openValue;
    protected int $userId;
    protected int $savedListId = 0;

    public static function getAllowStatuses(): array
    {
        return [
            'all',
            'opened',
            'unopened',
        ];
    }

    public function getOpenValue(): string
    {
        return $this->openValue;
    }

    public function setOpenValue($openValue): self
    {
        $this->openValue = $openValue;

        return $this;
    }

    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSavedListId(): int
    {
        return $this->savedListId;
    }

    public function setSavedListId(int $savedListId): self
    {
        $this->savedListId = $savedListId;

        return $this;
    }

    public function apply(Builder $builder, Model $model)
    {
        if ($this->getOpenValue() == 'all') {
            return;
        }

        $table   = $model->getTable();
        $listId  = $this->getSavedListId() == 0 ? null : $this->getSavedListId();

        $query = SavedListItemView::query()->getModel()->where([
            'user_id' => $this->getUserId(),
        ]);

        if ($listId) {
            $query->where('list_id', $listId);
        } else {
            $query->whereNull('list_id');
        }

        $viewIds = $query->pluck('saved_id')->toArray();

        switch ($this->getOpenValue()) {
            case 'opened':
                $builder->whereIn($this->alias($table, 'id'), $viewIds);
                break;
            default:
                $builder->whereNotIn($this->alias($table, 'id'), $viewIds);
        }
    }
}
