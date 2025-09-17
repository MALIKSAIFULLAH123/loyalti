<?php

namespace MetaFox\Platform\Traits\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait OrderCategoryTrait
{
    protected $orderSuccessMessage = 'core::phrase.categories_successfully_ordered';

    public function order(Request $request): JsonResponse
    {
        $orderIds = $request->get('order_ids');

        $this->repository->orderCategories($orderIds);

        return $this->success([], [], __p($this->orderSuccessMessage));
    }
}
