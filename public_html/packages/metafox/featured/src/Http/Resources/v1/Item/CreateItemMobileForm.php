<?php

namespace MetaFox\Featured\Http\Resources\v1\Item;

use Illuminate\Http\Request;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Repositories\PackageRepositoryInterface;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateItemForm
 * @property Content $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateItemMobileForm extends AbstractForm
{
    /**
     * @var Content
     */
    protected Content $item;

    protected function prepare(): void
    {
        $this->title(__p('featured::phrase.feature_item'))
            ->action('featured/item')
            ->asPost()
            ->setValue([
                'item_id'   => $this->resource->entityId(),
                'item_type' => $this->resource->entityType(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('item_title')
                    ->plainText(__p('featured::phrase.choose_package_description', [
                        'label'     => __p_type_key($this->resource->entityType()),
                        'item_link' => $this->resource->toLink(),
                        'item_name' => Feature::getItemTitle($this->resource),
                    ])),
                Builder::choice('package_id')
                    ->label(__p('featured::phrase.package'))
                    ->required()
                    ->options($this->getPackageOptions())
                    ->yup(
                        Yup::number()
                            ->required(),
                    ),
            );
    }

    protected function getPackageOptions(): array
    {
        return resolve(PackageRepositoryInterface::class)->getPackageOptionsForEntityType(user(), $this->resource->entityType());
    }

    /*
     * TODO: Remove after FE supported
     */
    public function boot(Request $request)
    {
        $this->resource = Feature::morphItemFromEntityType($request->get('item_type'), $request->get('item_id'));
        if ($this->resource instanceof HasPrivacy && $this->resource->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }
    }
}
