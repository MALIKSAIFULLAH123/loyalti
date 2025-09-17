<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Http\Resources\v1\Account;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as Model;
use MetaFox\Yup\Yup;

/**
 * Class EditLanguageMobileForm.
 * @property User $resource
 *
 * @driverType form-mobile
 * @driverName user.account.language
 */
class EditLanguageMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    public function boot(): void
    {
        $this->resource = user();
    }

    protected function prepare(): void
    {
        $profile = $this->resource instanceof Model ? $this->resource->profile : null;

        $this->title(__p('core::phrase.language'))
            ->asPut()
            ->action(url_utility()->makeApiUrl('/account/setting'))
            ->setValue([
                'language_id' => $profile?->language_id,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::choice('language_id')
                ->label(__p('core::phrase.primary_language'))
                ->placeholder(__p('core::phrase.primary_language'))
                ->autoComplete('off')
                ->required()
                ->options(Language::getActiveOptions())
                ->yup(Yup::string()->required()),
        );
    }
}
