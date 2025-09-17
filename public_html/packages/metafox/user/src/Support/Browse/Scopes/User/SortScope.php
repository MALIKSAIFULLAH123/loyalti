<?php

namespace MetaFox\User\Support\Browse\Scopes\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope as BaseScope;

class SortScope extends BaseScope
{
    public const SORT_DEFAULT      = self::SORT_FULL_NAME;
    public const SORT_TYPE_DEFAULT = Browse::SORT_TYPE_DESC;

    public const SORT_FULL_NAME     = 'full_name';
    public const SORT_ID            = 'id';
    public const SORT_GROUP         = 'group';
    public const SORT_LAST_LOGIN    = 'last_login';
    public const SORT_LAST_ACTIVITY = 'last_activity';
    public const SORT_CREATED_AT    = 'created_at';
    public const SORT_IP_ADDRESS    = 'ip_address';

    /**
     * @return array<int, string>
     */
    public static function getAllowSort(): array
    {
        return Arr::pluck(self::getSortOptions(), 'value');
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getSortOptions(): array
    {
        return [
            [
                'label' => __p('user::phrase.display_name'),
                'value' => self::SORT_FULL_NAME,
            ],
            [
                'label' => __p('user::phrase.last_login'),
                'value' => self::SORT_LAST_LOGIN,
            ],
            [
                'label' => __p('user::phrase.last_activity'),
                'value' => self::SORT_LAST_ACTIVITY,
            ],
            [
                'label' => __p('core::web.joined'),
                'value' => self::SORT_CREATED_AT,
            ],
            [
                'label' => __p('core::phrase.role'),
                'value' => self::SORT_GROUP,
            ],
            [
                'label' => __p('user::phrase.ip_address'),
                'value' => self::SORT_IP_ADDRESS,
            ],
        ];
    }

    /**
     * @return string
     */
    public static function getSortDefault(): string
    {
        return Settings::get('user.browse_user_default_order', self::SORT_FULL_NAME);
    }

    /**
     * @param  ?string $sort
     * @return string
     */
    public static function getDefaultSortType(?string $sort = null): string
    {
        return $sort == self::SORT_FULL_NAME ? Browse::SORT_TYPE_ASC : Browse::SORT_TYPE_DESC;
    }

    /**
     * @var string
     */
    private string $sort = self::SORT_DEFAULT;

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     *
     * @return self
     */
    public function setSort(string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();

        $sort            = $this->getSort();
        $sortType        = $this->getSortType();
        $aliasActivities = 'sortActivities';
        
        if (in_array($sort, [self::SORT_IP_ADDRESS, self::SORT_LAST_LOGIN, self::SORT_LAST_ACTIVITY])) {
            $builder->join("user_activities as $aliasActivities", "$aliasActivities.id", '=', $this->alias($table, 'id'));
        }

        switch ($sort) {
            case self::SORT_FULL_NAME:
                $fullNameColumn = "$table.full_name";

                $builder->orderByRaw("CASE WHEN ($fullNameColumn = '' OR $fullNameColumn IS NULL) THEN $table.user_name ELSE $fullNameColumn END $sortType");
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_LAST_LOGIN:
                $builder->orderBy($this->alias($aliasActivities, "last_login"), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_LAST_ACTIVITY:
                $builder->orderBy($this->alias($aliasActivities, 'last_activity'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_CREATED_AT:
                $builder->orderBy($this->alias($table, 'created_at'), $sortType);
                break;
            case self::SORT_ID:
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_GROUP:
                $builder->join('auth_model_has_roles', 'auth_model_has_roles.model_id', '=', $this->alias($table, 'id'))
                    ->join('auth_roles', 'auth_model_has_roles.role_id', '=', 'auth_roles.id')
                    ->orderBy('auth_roles.name', $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
            case self::SORT_IP_ADDRESS:
                $builder->orderBy($this->alias($aliasActivities, 'last_ip_address'), $sortType);
                $builder->orderBy($this->alias($table, 'id'), $sortType);
                break;
        }
    }
}
