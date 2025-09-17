<?php

namespace MetaFox\Profile\Providers;

use Illuminate\Support\ServiceProvider;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\Profile\Contracts\CustomFieldSupportInterface;
use MetaFox\Profile\Contracts\CustomProfileInterface;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Models\Option;
use MetaFox\Profile\Observers\FieldObserver;
use MetaFox\Profile\Repositories\Eloquent\FieldRepository;
use MetaFox\Profile\Repositories\Eloquent\OptionRepository;
use MetaFox\Profile\Repositories\Eloquent\ProfileRepository;
use MetaFox\Profile\Repositories\Eloquent\SectionRepository;
use MetaFox\Profile\Repositories\Eloquent\StructureRepository;
use MetaFox\Profile\Repositories\Eloquent\ValueRepository;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Repositories\OptionRepositoryInterface;
use MetaFox\Profile\Repositories\ProfileRepositoryInterface;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Profile\Repositories\StructureRepositoryInterface;
use MetaFox\Profile\Repositories\ValueRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\CustomProfile;

/**
 * --------------------------------------------------------------------------
 * Code Generator
 * --------------------------------------------------------------------------
 * stub: src/Providers/PackageServiceProvider.stub.
 */

/**
 * Class PackageServiceProvider.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class PackageServiceProvider extends ServiceProvider
{
    public array $singletons = [
        FieldRepositoryInterface::class     => FieldRepository::class,
        OptionRepositoryInterface::class    => OptionRepository::class,
        ProfileRepositoryInterface::class   => ProfileRepository::class,
        StructureRepositoryInterface::class => StructureRepository::class,
        SectionRepositoryInterface::class   => SectionRepository::class,
        ValueRepositoryInterface::class     => ValueRepository::class,
        CustomFieldSupportInterface::class  => CustomField::class,
        CustomProfileInterface::class       => CustomProfile::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        Field::observe([EloquentModelObserver::class, FieldObserver::class]);
        Option::observe([EloquentModelObserver::class]);
    }
}
