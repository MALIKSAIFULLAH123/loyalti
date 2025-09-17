<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use Illuminate\Support\Arr;
use MetaFox\App\Models\Package;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * Class EditModuleForm.
 * @property Package $resource
 */
class EditPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $value = $this->resource->toArray();

        Arr::forget($value, ['name', 'path', 'alias', 'latest_version', 'is_core']);

        $this
            ->title('Edit Module')
            ->action('/admincp/app/package/' . $this->resource->id)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('title')
                ->label('Title')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('frontend')
                ->label('Frontend Package')
                ->description('etc: @metafox/module-video:1.0.0'),
            Builder::textArea('description')
                ->label('Description')
                ->required(false),
            Builder::text('keywords')
                ->label('Keywords')
                ->required()
                ->yup(
                    Yup::string()
                ),
            Builder::text('author')
                ->label('author')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('author_url')
                ->label('Author Url')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->url()
                ),
            Builder::text('internal_url')
                ->label('Internal Url'),
            Builder::text('internal_admin_url')
                ->label('Admin Url')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('priority')
                ->label('Priority')
                ->required()
                ->yup(
                    Yup::number()
                        ->positive(true)
                        ->required()
                ),
            Builder::text('version')
                ->label('Version')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                ),
        );

        $this->addDefaultFooter(true);
    }
}
