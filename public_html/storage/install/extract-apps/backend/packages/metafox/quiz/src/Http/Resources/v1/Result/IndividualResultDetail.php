<?php

namespace MetaFox\Quiz\Http\Resources\v1\Result;

use MetaFox\Platform\Facades\ResourceGate;

class IndividualResultDetail extends ResultDetail
{
    public function toArray($request): array
    {
        $response = parent::toArray($request);

        $response['user']          = ResourceGate::user($this->resource->userEntity);
        $response['module_name']   = 'quiz';
        $response['resource_name'] = 'quiz_view_individual';
        $response['id']            = $this->resource->quiz_id;

        return $response;
    }
}
