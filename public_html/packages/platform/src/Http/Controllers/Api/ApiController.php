<?php

namespace MetaFox\Platform\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Fox4JsonResponse;

abstract class ApiController extends Controller
{
    use Fox4JsonResponse;

    public function getUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user == null) {
            abort(403);
        }

        return $user;
    }
}
