<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\App\Support\MetaFoxNews;
use MetaFox\App\Support\MetaFoxStore;
use MetaFox\Core\Http\Requests\v1\Dashboard\ChartDataRequest;
use MetaFox\Core\Http\Resources\v1\AdminAccess\AdminAccessItemCollection;
use MetaFox\Core\Http\Resources\v1\Statistic\ChartData;
use MetaFox\Core\Http\Resources\v1\Statistic\StatisticItemCollection;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Core\Repositories\AdminAccessRepositoryInterface;
use MetaFox\Core\Repositories\StatsContentRepositoryInterface;
use MetaFox\Core\Support\Facades\License;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;

class DashboardAdminController extends ApiController
{
    private AdminAccessRepositoryInterface $accessRepository;

    private StatsContentRepositoryInterface $statRepository;

    public function __construct(
        AdminAccessRepositoryInterface  $accessRepository,
        StatsContentRepositoryInterface $statRepository,
    )
    {
        $this->accessRepository = $accessRepository;
        $this->statRepository   = $statRepository;
    }

    public function deepStatistic(): JsonResponse
    {
        $data = $this->statRepository->getDeepStatistic();

        return $this->success($data);
    }

    public function itemStatistic(): JsonResponse
    {
        $data = $this->statRepository->getItemStatistic();

        return $this->success(new StatisticItemCollection($data));
    }

    public function adminLogged(Request $request): JsonResource
    {
        $limit  = $request->get('limit', 2); //@todo: move this to a setting??!
        $result = $this->accessRepository->getLatestAccesses($limit);

        return new AdminAccessItemCollection($result);
    }

    /**
     * @throws AuthenticationException
     */
    public function activeAdmin(): JsonResource
    {
        $limit  = 3; //@todo: move this to a setting??!
        $result = $this->accessRepository->getActiveUsers(user(), $limit);

        return new AdminAccessItemCollection($result);
    }

    public function siteStatus(Request $request): JsonResponse
    {
        $isReloading = $request->get('reload', false);
        if ($isReloading) {
            License::refresh();
            resolve(MetaFoxStore::class)->verifyMetaFoxInfo();
        }

        $expired = Settings::get('core.license.expired_at');

        if (!$expired) {
            $expired = null;
        }

        $licenseStatus = License::isActive();
        $latestVersion = Settings::get('core.platform.latest_version');
        $isExpired     = Carbon::parse($expired)->lte(Carbon::now());

        return $this->success([
            'license_status'       => $licenseStatus ? 'active' : 'inactive',
            'license_status_label' => $licenseStatus ? __p('core::phrase.is_active') : __p('core::phrase.inactive'),
            'license_status_style' => $licenseStatus ? 'success' : 'error',
            'is_expired'           => $isExpired,
            'installed_at'         => Settings::get('core.platform.installed_at'),
            'updated_at'           => Settings::get('core.platform.upgraded_at'),
            'license_expired_at'   => $licenseStatus ? $expired : null,
            'version'              => MetaFox::getVersion(),
            'latest_version'       => $latestVersion,
            'can_upgrade'          => version_compare(MetaFox::getVersion(), $latestVersion, '<'),
            'app_channel'          => config('app.mfox_app_channel'),
        ]);
    }

    public function metafoxNews(): JsonResponse
    {
        $result = (new MetaFoxNews())->getNews();

        return $this->success($result);
    }

    public function viewChart(ChartDataRequest $request): JsonResponse
    {
        $params = $request->validated();

        $period = Arr::get($params, 'period', StatsContent::STAT_PERIOD_ONE_DAY);

        $data = $this->statRepository->getChartDataWithContextualUser($params);

        try {
            $clientCarbon = Carbon::parse(MetaFox::clientDate());
        } catch (\Throwable $th) {
            $clientCarbon = Carbon::now();
        }

        $start = match ($period) {
            StatsContent::STAT_PERIOD_ONE_MONTH => $clientCarbon->clone()->startOfDay()->subMonths(12)->utc(),
            StatsContent::STAT_PERIOD_ONE_WEEK  => $clientCarbon->clone()->startOfDay()->subWeeks(15)->utc(),
            default                             => $clientCarbon->clone()->startOfDay()->subDays(30)->utc(),
        };

        $end = $clientCarbon->endOfDay()->utc();

        return $this->success($data, [
            'from' => $start,
            'to'   => $end,
        ]);
    }

    /**
     * @param ChartDataRequest $request
     * @return JsonResponse
     * @deprecated
     */
    public function chartData(ChartDataRequest $request): JsonResponse
    {
        $params    = $request->validated();
        $data      = $this->statRepository->getChartData($params);
        $period    = Arr::get($params, 'period');
        $name      = Arr::get($params, 'name');
        $resources = new ChartData($data);
        $resources->setPeriod($period);
        $resources = $resources->toArray($request);

        // pick realtime or not ?
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->statRepository->getNowStats($period);

        $last = collect($rows)->filter(function ($row) use ($name) {
            return $name === Arr::get($row, 'name');
        })->pop();

        if (!empty($last)) {
            array_pop($resources);
            $resources[] = [
                'data' => $last['value'],
                'date' => $last['created_at'],
            ];
        }

        return $this->success($resources);
    }

    public function statType(): JsonResponse
    {
        return $this->success($this->statRepository->getStatTypes(['pending_user']));
    }
}
