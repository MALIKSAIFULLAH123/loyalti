<?php

namespace MetaFox\User\Presenters;

use MetaFox\User\Transformers\UserTransformer;
use Prettus\Repository\Presenter\FractalPresenter as Presenter;

/**
 * Class UserPresenter.
 * @todo    remove presenter, use resource collection.
 */
class UserPresenter extends Presenter
{
    protected $resourceKeyItem = 'user';

    protected $resourceKeyCollection = 'users';

    /**
     * Transformer.
     *
     * @return UserTransformer
     */
    public function getTransformer()
    {
        return new UserTransformer();
    }
}
