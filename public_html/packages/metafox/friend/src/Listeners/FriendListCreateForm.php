<?php

namespace MetaFox\Friend\Listeners;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm;
use MetaFox\Friend\Http\Resources\v1\FriendList\CreateFriendListForm;
use MetaFox\Friend\Http\Resources\v1\FriendList\CreateFriendListMobileForm;
use MetaFox\Platform\MetaFoxConstant;

class FriendListCreateForm
{
    public function handle(Request $request, string $resolution, ?string $action = null): ?AbstractForm
    {
        $form = match ($resolution) {
            MetaFoxConstant::RESOLUTION_WEB    => resolve(CreateFriendListForm::class),
            MetaFoxConstant::RESOLUTION_MOBILE => resolve(CreateFriendListMobileForm::class),
        };

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        if (is_string($action)) {
            $form->setAction($action);
        }

        return $form;
    }
}
