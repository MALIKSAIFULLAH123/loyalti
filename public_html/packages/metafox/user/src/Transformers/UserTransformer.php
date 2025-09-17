<?php

namespace MetaFox\User\Transformers;

use League\Fractal\TransformerAbstract;
use MetaFox\User\Models\User;

class UserTransformer extends TransformerAbstract
{
    /**
     * Transform the User model.
     *
     * @param User $model
     *
     * @return array<string, mixed>
     */
    public function transform(User $model)
    {
        return $model->toArray();
    }
}
