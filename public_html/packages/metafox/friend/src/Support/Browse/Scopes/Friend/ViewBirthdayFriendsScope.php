<?php

namespace MetaFox\Friend\Support\Browse\Scopes\Friend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewBirthdayFriendsScope.
 * @ignore
 * @codeCoverageIgnore
 */
class ViewBirthdayFriendsScope extends BaseScope
{
    public const VIEW_TODAY    = 'today';
    public const VIEW_UPCOMING = 'upcoming';
    public const VIEW_MONTH    = 'month';
    /**
     * @var string
     */
    private string $view  = Browse::VIEW_ALL;
    private int    $month = 0;
    private User   $user;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
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
     * @return $this
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @param string $month
     *
     * @return $this
     */
    public function setMonth(int $month): self
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param Model   $model
     * @return \Illuminate\Database\Query\Builder|void
     */
    public function apply(Builder $builder, Model $model)
    {
        $view           = $this->getView();
        $month          = $this->getMonth();
        $now            = Carbon::now();
        $dayToCheck     = Settings::get('friend.days_to_check_for_birthday', 7);
        $totalDayOfYear = 366; //leap year
        $fromDay        = $now->dayOfYear + getDayOfLeapYearNumber($now);
        $toDate         = Carbon::now()->addDays($dayToCheck);
        $toDay          = $toDate->dayOfYear + getDayOfLeapYearNumber($toDate);
        $toDay          = $toDay > $totalDayOfYear ? $toDay - $totalDayOfYear : $toDay;

        switch ($view) {
            case self::VIEW_TODAY:
                $builder->where(function (Builder $query) use ($fromDay) {
                    $query->where('user_profiles.birthday_doy', $fromDay);
                });
                break;
            case self::VIEW_UPCOMING:
                $builder->where(function (Builder $query) use ($fromDay, $toDay, $totalDayOfYear) {
                    if ($fromDay <= $toDay) {
                        $query->whereBetween('user_profiles.birthday_doy', [$fromDay, $toDay]);
                    }

                    if ($fromDay > $toDay) {
                        $query->where(function (Builder $query) use ($fromDay, $toDay, $totalDayOfYear) {
                            $query->orWhereBetween('user_profiles.birthday_doy', [$fromDay, $totalDayOfYear]);
                            $query->orWhereBetween('user_profiles.birthday_doy', [1, $toDay]);
                        });
                    }
                });
                break;
            case self::VIEW_MONTH:
                $builder->where(function (Builder $query) use ($month) {
                    $query->where('user_profiles.birthday_month', $month);
                });
                break;
        }
    }
}
