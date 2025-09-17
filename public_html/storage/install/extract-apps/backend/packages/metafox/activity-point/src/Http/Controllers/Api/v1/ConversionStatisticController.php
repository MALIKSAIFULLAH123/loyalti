<?php

namespace MetaFox\ActivityPoint\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\ActivityPoint\Http\Resources\v1\ConversionStatistic\StatisticDetail as Detail;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\ActivityPoint\Models\ConversionStatistic;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\ActivityPoint\Http\Controllers\Api\ConversionStatisticController::$controllers;
 */

/**
 * Class ConversionStatisticController
 * @codeCoverageIgnore
 * @ignore
 */
class ConversionStatisticController extends ApiController
{
    /**
     * View item
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show($id): Detail
    {
        $context = user();

        $user = UserEntity::getById($id)->detail;

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        if ($context->entityId() != $user->entityId()) {
            throw new AuthorizationException();
        }

        $statistic = ConversionStatistic::query()
            ->firstOrCreate([
                'user_id' => $user->entityId(),
                'user_type' => $user->entityType(),
            ]);

        if ($statistic->wasRecentlyCreated) {
            $statistic->refresh();
        }

        return new Detail($statistic);
    }
}
