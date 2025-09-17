<?php

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use MetaFox\Platform\Checksum;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

class SecurityAdminController extends ApiController
{

    public function changedFiles(): JsonResponse
    {
        $items = array_map(function ($item) {
            return ['label' => $item['name'], 'value' => $item['status']];
        }, Checksum::testChecksum());


        return $this->success([
            'title'    => __p('core::phrase.change_files'),
            'sections' => [
                [
                    'title' => __p('core::phrase.change_files'),
                    'items' => count($items)? $items: [
                        ['label'=> "There are no changed files", "value"=> '']
                    ],
                ]
            ]
        ]);
    }
}