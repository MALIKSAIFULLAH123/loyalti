<?php

namespace MetaFox\LiveStreaming\Support\Browse\Scopes\LiveVideo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 * @ignore
 * @codeCoverageIgnore
 */
class DurationScope extends BaseScope
{
    public const DURATION_LONGER  = 'longer';
    public const DURATION_SHORTER = 'shorter';

    /**
     * @param string $duration
     */
    public function __construct(protected string $duration = '')
    {
    }

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return ['sometimes', 'nullable', 'string', 'in:' . implode(',', static::getAllowView())];
    }

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::DURATION_LONGER,
            self::DURATION_SHORTER,
        ];
    }

    /**
     * @return string
     */
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return int
     */
    public static function getSetting(): int
    {
        return (int) Settings::get('livestreaming.filter_streaming_content_by_minutes', 0);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $duration = $this->getDuration();
        if (!$duration || !self::getSetting()) {
            return;
        }

        switch ($duration) {
            case self::DURATION_LONGER:
                $builder->when(function (Builder $builder) {
                    $builder->where(function ($query) {
                        $query->where('livestreaming_live_videos.duration', '>=', self::getSetting() * 60)
                            ->whereNotNull('livestreaming_live_videos.duration');
                    });
                });
                break;
            case self::DURATION_SHORTER:
                $builder->when(function (Builder $builder) {
                    $builder->where(function ($query) {
                        $query->where('livestreaming_live_videos.duration', '<=', self::getSetting() * 60)
                            ->orWhereNull('livestreaming_live_videos.duration');
                    });
                });
                break;
            default:
                break;
        }
    }
}
