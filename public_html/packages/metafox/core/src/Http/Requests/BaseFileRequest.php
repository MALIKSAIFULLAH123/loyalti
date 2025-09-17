<?php

namespace MetaFox\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class BaseFileRequest extends FormRequest
{
    protected function validateNullStorageId(array $data): array
    {
        /**
         * If has a disk with name is null.
         */
        $validator = Validator::make(['storage_id' => Arr::get($data, 'storage_id')], [
            'storage_id' => ['exists:storage_disks,name'],
        ]);

        if ($validator->passes()) {
            return $data;
        }

        unset($data['storage_id']);

        return $data;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        /*
         * In case client post an null value but web server convert null to "null"
         */
        if (Arr::get($data, 'storage_id') == 'null') {
            $data = $this->validateNullStorageId($data);
        }

        return $data;
    }

    protected function getStorageIdRule(): array
    {
        return [
            'storage_id' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
