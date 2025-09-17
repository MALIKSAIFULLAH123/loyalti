<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\User\Models\UserGender as Model;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;

/**
 * Class Gender.
 */
class Gender extends Choice
{
    public function initialize(): void
    {
        parent::initialize();
        $this->name('gender')
            ->label(__p('user::phrase.user_gender'))
            ->initOptions();
    }

    protected function initOptions(): self
    {
        $genders = resolve(UserGenderRepositoryInterface::class)->getModel()->newModelQuery()->get();

        $default = [
            ['label' => __p('core::phrase.all'), 'value' => 0],
        ];

        $options = collect($genders)->map(function (Model $gender) {
            return [
                'label' => $gender->name,
                'value' => $gender->entityId(),
            ];
        })->toArray();

        return $this->options(array_merge($default, $options));
    }
}
