<?php

namespace MetaFox\EMoney\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\EMoney\Http\Resources\v1\Statistic\StatisticDetail as Detail;
use MetaFox\EMoney\Repositories\StatisticRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;

/**
 | --------------------------------------------------------------------------
 |  Api Controller
 | --------------------------------------------------------------------------
 |
 | stub: /packages/controllers/api_controller.stub
 | Assign this class in $controllers of
 | @link \MetaFox\EMoney\Http\Controllers\Api\StatisticController::$controllers;
 */

/**
 * Class StatisticController.
 * @codeCoverageIgnore
 * @ignore
 */
class StatisticController extends ApiController
{
    /**
     * @var StatisticRepositoryInterface
     */
    private StatisticRepositoryInterface $repository;

    /**
     * StatisticController Constructor.
     *
     * @param StatisticRepositoryInterface $repository
     */
    public function __construct(StatisticRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return Detail
     */
    public function show(int $id): Detail
    {
        $context = user();

        $user = UserEntity::getById($id)->detail;

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        if (!$context->hasSuperAdminRole() && $context->entityId() != $user->entityId()) {
            throw new AuthorizationException();
        }

        $data = $this->repository->getStatistic($user, Support::DEFAULT_TARGET_CURRENCY_CONVERSION_RATE);

        return new Detail($data);
    }
}
