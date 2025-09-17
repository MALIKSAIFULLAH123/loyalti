<?php

namespace MetaFox\LiveStreaming\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use MetaFox\LiveStreaming\Models\StreamingService as Model;
use MetaFox\LiveStreaming\Repositories\StreamingServiceRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StreamingServiceRepository.
 */
class StreamingServiceRepository extends AbstractRepository implements StreamingServiceRepositoryInterface
{
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function viewServices(User $context, array $params = []): Paginator
    {
        $limit = $params['limit'];

        return $this->getModel()->newModelQuery()->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function updateService(User $context, int $id, array $params = []): Model
    {
        $service = $this->find($id);

        $service->fill($params);
        $service->save();

        return $service;
    }

    /**
     * @inheritDoc
     */
    public function getServicesOptions(): array
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('is_active', '=', 1)
            ->get()
            ->collect()
            ->map(function (Model $service) {
                return [
                    'label' => $service->name,
                    'value' => $service->driver,
                ];
            })
            ->values()
            ->toArray();
    }
}
