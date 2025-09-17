<?php

namespace MetaFox\Advertise\Support\Browse\Scopes\Advertise;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MetaFox\Advertise\Support\Support;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class StatusScope extends BaseScope
{
    public function __construct(protected ?string $status = null)
    {
    }

    public function apply(Builder $builder, Model $model)
    {
        if (null === $this->status) {
            return;
        }

        switch ($this->status) {
            case Support::ADVERTISE_STATUS_UPCOMING:
                $builder->where('advertises.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                    ->where('advertises.start_date', '>', Carbon::now());
                break;
            case Support::ADVERTISE_STATUS_RUNNING:
                $builder->where('advertises.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                    ->where('advertises.start_date', '<=', Carbon::now())
                    ->where(function ($builder) {
                        $builder->whereNull('advertises.end_date')
                            ->orWhere('advertises.end_date', '>', Carbon::now());
                    });
                break;
            case Support::ADVERTISE_STATUS_ENDED:
                $builder->where('advertises.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                    ->whereNotNull('advertises.end_date')
                    ->where('advertises.end_date', '<=', Carbon::now());
                break;
            default:
                $builder->where('advertises.status', '=', $this->status);
                break;
        }
    }
}
