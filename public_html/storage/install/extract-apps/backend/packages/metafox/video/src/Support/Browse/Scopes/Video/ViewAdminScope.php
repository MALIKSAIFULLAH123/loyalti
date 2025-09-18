<?php

namespace MetaFox\Video\Support\Browse\Scopes\Video;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;
use MetaFox\Video\Models\Video;

class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT  = Browse::VIEW_ALL;
    public const VIEW_FAILED   = 'failed';
    public const VIEW_PROCESS  = 'process';
    public const VIEW_APPROVED = 'approved';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return Arr::pluck(self::getViewOptions(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function getViewOptions(): array
    {
        return [
            [
                'value' => Browse::VIEW_ALL,
                'label' => __p('core::phrase.all'),
            ],
            [
                'value' => self::VIEW_APPROVED,
                'label' => __p('core::phrase.approved'),
            ],
            [
                'value' => Browse::VIEW_PENDING,
                'label' => __p('core::phrase.pending'),
            ],
            [
                'value' => Browse::VIEW_FEATURE,
                'label' => __p('core::phrase.featured'),
            ],
            [
                'value' => Browse::VIEW_SPONSOR,
                'label' => __p('core::web.sponsored'),
            ],
            [
                'value' => self::VIEW_FAILED,
                'label' => __p('core::phrase.failed'),
            ],
            [
                'value' => self::VIEW_PROCESS,
                'label' => __p('core::phrase.processing'),
            ],
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ViewAdminScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewAdminScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        $view  = $this->getView();

        switch ($view) {
            case Browse::VIEW_PENDING:
                $builder->where("$table.is_approved", '!=', 1);
                $builder->where("$table.in_process", '=', Video::STATUS_READY);
                break;
            case self::VIEW_APPROVED:
                $builder->where("$table.is_approved", '=', 1);
                $builder->where("$table.in_process", '=', Video::STATUS_READY);
                break;
            case Browse::VIEW_FEATURE:
                $builder->where("$table.is_featured", '=', 1);
                $builder->where("$table.in_process", '=', Video::STATUS_READY);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where("$table.is_sponsor", '=', 1);
                $builder->where("$table.in_process", '=', Video::STATUS_READY);
                break;
            case self::VIEW_FAILED:
                $builder->where("$table.in_process", '=', Video::STATUS_FAILED);
                break;
            case self::VIEW_PROCESS:
                $builder->where("$table.in_process", '=', Video::STATUS_PROCESS);
                break;
        }
    }
}
