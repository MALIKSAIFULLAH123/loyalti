<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\ActivityPoint\Models\PointPackage as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/detail.stub
*/

/**
 * Class PointPackageDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @mixin Model
 */
class PointPackageDetail extends \MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\PointPackageDetail
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
