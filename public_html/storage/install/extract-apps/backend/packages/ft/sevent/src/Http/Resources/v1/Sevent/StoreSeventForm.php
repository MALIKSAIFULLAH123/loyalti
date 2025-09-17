<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\CreateFormRequest;
use Foxexpert\Sevent\Models\Sevent as Model;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;
use Illuminate\Support\Carbon;
/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreSeventForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreSeventForm extends AbstractForm
{
    use PrivacyFieldTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, SeventRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        policy_authorize(SeventPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
        if (!empty($params['course_id'])) {
            $this->resource->course_id = $params['course_id'];
        }
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'sevent.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }
        
        $this->title(__p('sevent::phrase.add_new_sevent'))
            ->action(url_utility()->makeApiUrl('sevent'))
            ->asPost()
            ->setBackProps(__p('core::web.sevent'))
            ->setValue([
                'title'       => '',
                'is_online'   => (!Settings::get('sevent.enable_location') 
                    and Settings::get('sevent.enable_online')) ? 1 : 0,
                'is_host'     => 0,
                'course_id'     => $this->resource->course_id ? $this->resource->course_id : 0,
                'module_id'   => 'sevent',
                'start_date'  => Carbon::now()->toISOString(),
                'end_date'    => Carbon::now()->addHour()->toISOString(),
                'privacy'     => $privacy,
                'location'    => null,
                'draft'       => 0,
                'tags'        => [],
                'owner_id'    => $this->resource->owner_id,
                'attachments' => [],
                'categories'  => [],
            ]);
    }

    protected function getDisplayFormat(int $value): string
    {
        $displayFormat = [
            12 => MetaFoxConstant::DISPLAY_FORMAT_TIME_12,
            24 => MetaFoxConstant::DISPLAY_FORMAT_TIME_24,
        ];

        return $displayFormat[$value];
    }

    protected function initialize(): void
    {
        $basic              = $this->addBasic();
        $privacyField       = $this->buildPrivacyField()
            ->description(__p('sevent::phrase.control_who_can_see_this_sevent'));
        $locationField = $this->getLocationField();
        $multiplePhotos = $this->addAttachedPhotosField();
        $timeFormat = Settings::get('sevent.time_format');
        $videoField = $this->getVideoField();
        $termsField = $this->getTerms();

        $basic->addFields(
            Builder::text('title')
                ->required()
                ->marginNormal()
                ->label(__p('sevent::phrase.title_field'))
                ->placeholder(__p('sevent::phrase.fill_in_a_title_for_your_sevent'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::richTextEditor('text')
                ->required()
                ->label(__p('sevent::phrase.description_field'))
                ->placeholder(__p('sevent::phrase.add_some_content_to_your_sevent'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::textArea('short_description')
                ->label(__p('sevent::phrase.short_description'))
                ->placeholder(__p('sevent::phrase.short_description')),
            Builder::category('categories')
                ->multiple(true)
                ->sizeLarge()
                ->setRepository(CategoryRepositoryInterface::class),    
            Builder::checkbox('is_online')
                ->multiple(false)
                ->disabled((!Settings::get('sevent.enable_location') 
                and Settings::get('sevent.enable_online') or Settings::get('sevent.enable_location') 
                and !Settings::get('sevent.enable_online')) ? true : false)
                ->label(__p('sevent::phrase.set_online_event')),   
            Builder::text('online_link')
            ->returnKeyType('next')
            ->nullable(true)
            ->label(__p('sevent::phrase.event_url'))
            ->placeholder(__p('sevent::phrase.paste_your_event_url_here'))
            ->showWhen(['eq', 'is_online', 1])
            ->requiredWhen(['eq', 'is_online', 1])
            ->yup(
                Yup::string()
                    ->nullable()
                    ->when(
                        Yup::when('is_online')
                            ->is(1)
                            ->then(
                                Yup::string()
                                    ->required()
                                    ->url()
                                    ->setError('required', __p('validation.this_field_is_a_required_field'))
                            )
                    )
            ),         
            $locationField,
        Builder::datetime('start_date')
            ->returnKeyType('next')
            ->required(true)
            ->displayFormat($this->getDisplayFormat($timeFormat))
            ->timeFormat($timeFormat)
            ->timeSuggestion(true)
            ->labelTimePicker(__p('sevent::phrase.start_time'))
            ->labelDatePicker(__p('sevent::phrase.start_date'))
            ->yup(
                Yup::date()
                    ->required(__p('validation.this_field_is_a_required_field'))
            ),
        Builder::datetime('end_date')
            ->returnKeyType('next')
            ->required(true)
            ->displayFormat($this->getDisplayFormat($timeFormat))
            ->timeFormat($timeFormat)
            ->timeSuggestion(true)
            ->labelTimePicker(__p('sevent::phrase.end_time'))
            ->labelDatePicker(__p('sevent::phrase.end_date'))
            ->yup(
                Yup::date()
                    ->required(__p('validation.this_field_is_a_required_field'))
            ),
            Builder::singlePhoto()
                ->label(__p('sevent::phrase.main_photo'))
                ->widthPhoto('300px')
                ->aspectRatio('16:9')
                ->itemType('sevent')
                ->thumbnailSizes($this->resource->getSizes())
                ->previewUrl($this->resource->image ? $this->resource->image : ''),
            $multiplePhotos,
            $videoField,
            $termsField,
            Builder::attachment()
                ->itemType('sevent')
        );

        // host data
        Settings::get('sevent.enable_host') ?
            $basic->addFields(
                Builder::checkbox('is_host')
                    ->multiple(false)
                    ->label(__p('sevent::phrase.add_host')), 
                Builder::text('host_title')
                    ->marginNormal()
                    ->showWhen(['eq', 'is_host', 1])
                    ->label(__p('sevent::phrase.host_title')),
                Builder::text('host_contact')
                    ->marginNormal()
                    ->showWhen(['eq', 'is_host', 1])
                    ->label(__p('sevent::phrase.host_contact')),
                Builder::text('host_website')
                    ->marginNormal()
                    ->showWhen(['eq', 'is_host', 1])
                    ->label(__p('sevent::phrase.host_website'))
                    ->yup(
                        Yup::string()
                            ->nullable()
                            ->when(
                                Yup::when('is_host')
                                    ->is(1)
                                    ->then(
                                        Yup::string()
                                            ->url()
                                            ->setError('required', __p('validation.this_field_is_a_required_field'))
                                    )
                            )
                    ),
                Builder::text('host_facebook')
                    ->marginNormal()
                    ->showWhen(['eq', 'is_host', 1])
                    ->label(__p('sevent::phrase.host_facebook'))
                    ->yup(
                        Yup::string()
                            ->nullable()
                            ->when(
                                Yup::when('is_host')
                                    ->is(1)
                                    ->then(
                                        Yup::string()
                                            ->url()
                                            ->setError('required', __p('validation.this_field_is_a_required_field'))
                                    )
                            )
                    ),

                Builder::textArea('host_description')
                    ->marginNormal()
                    ->showWhen(['eq', 'is_host', 1])
                    ->label(__p('sevent::phrase.host_description')),
                    
                Builder::singlePhoto('host_image')
                    ->label(__p('sevent::phrase.host_photo'))
                    ->widthPhoto('300px')
                    ->showWhen(['eq', 'is_host', 1])
                    ->aspectRatio('16:9')
                    ->itemType('sevent')
                    ->thumbnailSizes($this->resource->getSizes())
                    ->previewUrl($this->resource->host ? $this->resource->host : '')
            ) : null;

        $basic->addFields(
            $this->buildTagField(),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            Builder::hidden('course_id'),
            $privacyField,
            Captcha::getFormField('sevent.create_sevent')
        );

        $this->addFooter()
            ->addFields(
                $this->buildPublishButton(),
                $this->buildSaveAsDraftButton(),
                Builder::cancelButton()
                    ->sizeMedium(),
            );

        // force returnUrl as string
        $basic->addField(
            Builder::hidden('returnUrl')
        );
    }

    protected function getVideoField()
    {
        if (!app_active('ft/uvideo')) return null;

        $field = Builder::text('video')
            ->marginNormal()
            ->label(__p('sevent::phrase.video_field'))
            ->description(__p('sevent::phrase.video_field_desc'));

        return $field;
    }

    protected function addAttachedPhotosField()
    {
        $maxUpload = 10;
        $fileSize = file_type()->getFilesizeInMegabytes('photo');
        $field = Builder::uploadMultiMedia('attached_photos')
            ->label(__p('core::phrase.add_photos'))
            ->placeholder(__p('core::phrase.drag_and_drop_photos_upload'))
            ->description(__p('sevent::web.upload_attached_photos', [
                'max_file_size'        => $fileSize,
                'has_limit_per_upload' => $maxUpload > 0 ? 1 : 0,
                'max_per_upload'       => $maxUpload,
            ]))
            ->accepts('image/*')
            ->maxFiles($maxUpload)
            ->itemType('sevent')
            ->uploadUrl('file');

        return $field;
    }

    protected function getTerms()
    {
        if (!Settings::get('sevent.enable_terms')) return null;

        return  Builder::richTextEditor('terms')
            ->label(__p('sevent::phrase.terms_field'))
            ->placeholder(__p('sevent::phrase.terms_field'));
    }

    protected function getLocationField()
    {
        if (Settings::get('core.google.google_map_api_key') == null) {
            return Builder::text('location_name')
                ->label(__p('core::phrase.location'))
                ->requiredWhen(['eq', 'is_online', 0])
                ->showWhen(['eq', 'is_online', 0])
                ->yup(Yup::string()
                    ->nullable()
                    ->when(
                        Yup::when('is_online')
                            ->is(0)
                            ->then(
                                Yup::string()
                                    ->required(__p('sevent::phrase.location_is_a_required_field')))
                    ));
        }

        return Builder::location('location')
            ->placeholder(__p('sevent::phrase.enter_location'))
            ->requiredWhen(['eq', 'is_online', 0])
            ->showWhen(['eq', 'is_online', 0])
            ->yup(
                Yup::object()
                    ->nullable()
                    ->when(
                        Yup::when('is_online')
                            ->is(0)
                            ->then(
                                Yup::object()
                                    ->nullable()
                                    ->required()
                                    ->setError('required', __p('event::phrase.location_is_a_required_field'))
                                    ->setError('typeError', __p('event::phrase.location_is_a_required_field'))
                                    ->addProperty(
                                        'lat',
                                        Yup::number()
                                            ->nullable()
                                    )
                                    ->addProperty(
                                        'lng',
                                        Yup::number()
                                            ->nullable()
                                    )
                                    ->addProperty(
                                        'address',
                                        Yup::string()
                                            ->nullable()
                                    )
                                    ->addProperty(
                                        'short_name',
                                        Yup::string()
                                            ->nullable()
                                    )
                            )
                    )
                    ->addProperty(
                        'lat',
                        Yup::number()
                            ->nullable()
                    )
                    ->addProperty(
                        'lng',
                        Yup::number()
                            ->nullable()
                    )
                    ->addProperty(
                        'address',
                        Yup::string()
                            ->nullable()
                    )
                    ->addProperty(
                        'short_name',
                        Yup::string()
                            ->nullable()
                    )
            );
    }

    protected function buildPublishButton(): AbstractField
    {
        return Builder::submit()
            ->label(__p('core::phrase.publish'))
            ->flexWidth(true);
    }

    protected function buildSaveAsDraftButton(): AbstractField
    {
        return Builder::submit('draft')
            ->label(__p('core::phrase.save_as_draft'))
            ->color('primary')
            ->setValue(1)
            ->variant('outlined')
            ->showWhen(['falsy', 'published']);
    }

    protected function buildTagField(): ?AbstractField
    {
        if ($this->owner instanceof HasPrivacyMember) {
            return null;
        }

        return Builder::tags()
            ->label(__p('core::phrase.topics'))
            ->placeholder(__p('core::phrase.keywords'));
    }
}
