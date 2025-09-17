<?php

namespace MetaFox\Core\Http\Resources\v1\Statistic;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use MetaFox\Core\Models\StatsContent as Model;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;

/**
 * Class ChartData.
 * @property Collection $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ChartData extends JsonResource
{
    private string $period;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<int, mixed>
     */
    public function toArray($request): array
    {
        $this->initDefaultData($this->getPeriod());

        $this->fillData();

        return array_values($this->data);
    }

    /**
     * @return array<string>
     */
    protected function normalizePointData(Model $resource): array
    {
        return [
            'data' => $resource->value,
            'date' => Carbon::parse($resource->created_at)->startOfDay()->toIso8601String(),
        ];
    }

    private function initDefaultData(string $period): void
    {
        $this->data = resolve(StatsContentRepositoryInterface::class)->getEmptyChartData($period);
    }

    private function fillData(): void
    {
        $data = $this->resource
            ->map(function (Model $stat) {
                return $this->normalizePointData($stat);
            })->values()
            ->keyBy('date')
            ->toArray();

        $this->data = array_merge($this->data, $data);
    }

    /**
     * @return string
     */
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * @param  string    $period
     * @return ChartData
     */
    public function setPeriod(string $period): self
    {
        $this->period = $period;

        return $this;
    }
}
