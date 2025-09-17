<?php

namespace MetaFox\Contact\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use MetaFox\Contact\Http\Requests\v1\Contact\StoreRequest;
use MetaFox\Contact\Support\Facades\Contact;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * Class ContactController.
 * @codeCoverageIgnore
 * @ignore
 */
class ContactController extends ApiController
{
    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = Auth::user() ?? UserFacade::getGuestUser();

        if (!$context->hasPermissionTo('contact.create')) {
            throw new AuthorizationException();
        }

        try {
            Contact::send($params);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        return $this->success([], [], __p('contact::phrase.contact_message_successfully_sent'));
    }
}
