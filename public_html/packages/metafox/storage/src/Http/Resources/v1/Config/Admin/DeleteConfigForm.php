<?php

namespace MetaFox\Storage\Http\Resources\v1\Config\Admin;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class DeleteConfigForm
 * @ignore
 * @codeCoverageIgnore
 */
class DeleteConfigForm extends AbstractForm
{
    protected bool    $isBeingUsed        = false;
    protected int     $totalFileBeingUsed = 0;
    protected ?string $name               = null;

    public function boot(Request $request): void
    {
        $this->name         = $request->get('name');
        $totalFileBeingUsed = StorageFile::query()
            ->where('target', $this->name)
            ->where('is_origin', true)
            ->count();
        if ($totalFileBeingUsed > 0) {
            $this->isBeingUsed        = true;
            $this->totalFileBeingUsed = $totalFileBeingUsed;
        }
    }

    protected function prepare(): void
    {
        $this->title(__p('storage::phrase.remove_configuration_name'))
            ->action('/admincp/storage/option/' . $this->name)
            ->asDelete()
            ->setValue([
                'is_remove' => false,
                'name'      => $this->name,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();


        $basic->addFields(
            Builder::alert('_alert_confirm')
                ->asInfo()
                ->message(__p('storage::phrase.delete_configure_name_description', [
                    'total_file'     => $this->totalFileBeingUsed,
                    'configure_name' => $this->name,
                ])),
            Builder::text('confirm_name')
                ->required()
                ->label(__p('storage::phrase.confirmation_configuration_name'))
                ->yup(Yup::string()
                    ->matches($this->name)),
        );

        if ($this->totalFileBeingUsed > 0) {
            $basic->addFields(
                Builder::checkbox('is_remove')
                    ->label(__p('storage::phrase.remove_associated_files_title'))
                    ->description(__p('storage::phrase.remove_associated_files_description'))
            );
        }

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.submit')),
                Builder::cancelButton(),
            );
    }
}
