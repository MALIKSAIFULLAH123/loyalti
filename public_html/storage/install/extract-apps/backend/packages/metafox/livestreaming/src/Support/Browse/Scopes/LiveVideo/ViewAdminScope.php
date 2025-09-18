<?php

namespace MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewAdminScope extends BaseScope
{
    public const VIEW_DEFAULT       = Browse::VIEW_ALL;
    public const VIEW_ALL_STREAMING = 'all_streaming';
    public const VIEW_APPROVED      = 'approved';

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
                'value' => self::VIEW_ALL_STREAMING,
                'label' => __p('livestreaming::phrase.live_streaming'),
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
        ];
    }

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowView())];
    }

    /**
     * @var string
     */
    protected string $view = self::VIEW_DEFAULT;

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
     * @return ViewScope
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
     * @return ViewScope
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
        $view = $this->getView();

        // filter view_id != 2 (not livestream yet)
        $builder->where('livestreaming_live_videos.view_id', '!=', 2);

        switch ($view) {
            case self::VIEW_ALL_STREAMING:
                $builder->where('livestreaming_live_videos.is_streaming', '=', 1)
                    ->where('livestreaming_live_videos.is_approved', '=', 1);
                break;
            case Browse::VIEW_PENDING:
                $builder->where('livestreaming_live_videos.is_approved', '!=', 1);
                break;
            case self::VIEW_APPROVED:
                $builder->where('livestreaming_live_videos.is_approved', '=', 1);
                break;
            case Browse::VIEW_FEATURE:
                $builder->where("livestreaming_live_videos.is_featured", 1)
                    ->where("livestreaming_live_videos.is_approved", 1);
                break;
            case Browse::VIEW_SPONSOR:
                $builder->where("livestreaming_live_videos.is_sponsor", '=', 1)
                    ->where("livestreaming_live_videos.is_approved", 1);
                break;
        }
    }
}
