<?php

namespace MetaFox\User\Http\Resources\v1\InactiveProcess\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\User\Models\InactiveProcess as Model;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateInactiveProcessForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateInactiveProcessForm extends AbstractForm
{
    protected ?int  $days = null;
    protected array $ids  = [];

    protected function prepare(): void
    {
        $this->title(__p('user::phrase.process_mailing_confirm_title'))
            ->action(apiUrl('admin.user.inactive-process.store'))
            ->asPost()
            ->setValue([
                'round'     => 0,
                'owner_ids' => $this->ids,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        if (empty($this->ids)) {
            $basic->addField(
                Builder::typography('no_users_found')
                    ->plainText(__p('user.phrase.no_users_found_from_the_filter')),
            );

            return;
        }

        if ($this->days) {
            $basic->addField(
                Builder::typography('confirm')
                    ->plainText(__p('user::web.process_mailing_confirm_desc', ['day' => $this->days])),
            );
        }

        $basic->addFields(
            Builder::text('round')
                ->required()
                ->asNumber()
                ->label(__p('user::phrase.how_many_per_round'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->positive()
                        ->required()
                ),
            Builder::hidden('owner_ids')
        );

        $this->addDefaultFooter();
    }

    public function boot(Request $request)
    {
        $params = $request->all();

        /**@var UserAdminRepositoryInterface $userRepository */
        $userRepository = resolve(UserAdminRepositoryInterface::class);
        $users          = $userRepository->buildQueryViewUsers($params)->get();

        $this->ids = $users->pluck('id')->toArray();

        if (Arr::has($params, 'day')) {
            $this->days = Arr::get($params, 'day');
        }
    }
}
