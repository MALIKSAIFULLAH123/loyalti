<?php

namespace MetaFox\Advertise\Support\Browse\Scopes\Sponsor;

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
                $builder->where('advertise_sponsors.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                    ->where('advertise_sponsors.start_date', '>', Carbon::now());
                break;
            case Support::ADVERTISE_STATUS_RUNNING:
                $builder->where('advertise_sponsors.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                    ->where('advertise_sponsors.start_date', '<=', Carbon::now())
                    ->where(function ($builder) {
                        $builder->whereNull('advertise_sponsors.end_date')
                            ->orWhere('advertise_sponsors.end_date', '>', Carbon::now());
                    });
                break;
            case Support::ADVERTISE_STATUS_ENDED:
                $builder->where(function ($builder) {
                    $builder->where('advertise_sponsors.status', Support::ADVERTISE_STATUS_ENDED)
                        ->orWhere(function ($builder) {
                            $builder->where('advertise_sponsors.status', '=', Support::ADVERTISE_STATUS_APPROVED)
                                ->whereNotNull('advertise_sponsors.end_date')
                                ->where('advertise_sponsors.end_date', '<=', Carbon::now());
                        });
                });
                break;
            default:
                $builder->where('advertise_sponsors.status', '=', $this->status);
                break;
        }
    }
}
