<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;

class CreateFeedForm extends AbstractForm
{
    public function validated(Request $request): array
    {
        $data = $request->all();

        return $this->transformData($data);
    }

    /**
     * @param  array $data
     * @return array
     */
    protected function transformData(array $data): array
    {
        Arr::set($data, 'content', Arr::get($data, 'user_status', ''));

        return $data;
    }
}
