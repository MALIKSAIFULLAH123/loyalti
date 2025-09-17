<?php

namespace MetaFox\User\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use MetaFox\Authorization\Models\Role;
use MetaFox\Authorization\Observers\UserRoleObserver;
use MetaFox\Platform\Support\EloquentModelObserver;
use MetaFox\User\Contracts\PermissionRegistrar;
use MetaFox\User\Contracts\Support\ActionServiceManagerInterface;
use MetaFox\User\Contracts\UserAuth as ContractsUserAuth;
use MetaFox\User\Contracts\UserBirthday as UserBirthdayContracts;
use MetaFox\User\Contracts\UserBlockedSupportContract;
use MetaFox\User\Contracts\UserContract;
use MetaFox\User\Contracts\UserVerifySupportContract;
use MetaFox\User\Models\CancelReason;
use MetaFox\User\Models\ExportProcess;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserBan;
use MetaFox\User\Models\UserBlocked;
use MetaFox\User\Models\UserGender;
use MetaFox\User\Models\UserPrivacy as UserPrivacyModel;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Models\UserRelation;
use MetaFox\User\Models\UserRelationHistory;
use MetaFox\User\Models\UserShortcut;
use MetaFox\User\Models\UserValue as UserValueModel;
use MetaFox\User\Observers\UserBanObserver;
use MetaFox\User\Observers\UserEntityObserver;
use MetaFox\User\Observers\UserObserver;
use MetaFox\User\Observers\UserProfileObserver;
use MetaFox\User\Repositories\AdminLoggedRepositoryInterface;
use MetaFox\User\Repositories\CancelFeedbackAdminRepositoryInterface;
use MetaFox\User\Repositories\CancelReasonAdminRepositoryInterface;
use MetaFox\User\Repositories\Contracts\AccountSettingRepositoryInterface;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\Eloquent\AccountSettingRepository;
use MetaFox\User\Repositories\Eloquent\AdminLoggedRepository;
use MetaFox\User\Repositories\Eloquent\CancelFeedbackAdminRepository;
use MetaFox\User\Repositories\Eloquent\CancelReasonAdminRepository;
use MetaFox\User\Repositories\Eloquent\ExportProcessRepository;
use MetaFox\User\Repositories\Eloquent\InactiveProcessAdminRepository;
use MetaFox\User\Repositories\Eloquent\MultiFactorTokenRepository;
use MetaFox\User\Repositories\Eloquent\PasswordResetTokenRepository;
use MetaFox\User\Repositories\Eloquent\SocialAccountRepository;
use MetaFox\User\Repositories\Eloquent\UserAdminRepository;
use MetaFox\User\Repositories\Eloquent\UserBanRepository;
use MetaFox\User\Repositories\Eloquent\UserGenderRepository;
use MetaFox\User\Repositories\Eloquent\UserPasswordHistoryRepository;
use MetaFox\User\Repositories\Eloquent\UserPreferenceRepository;
use MetaFox\User\Repositories\Eloquent\UserPrivacyRepository;
use MetaFox\User\Repositories\Eloquent\UserProfileRepository;
use MetaFox\User\Repositories\Eloquent\UserPromotionRepository;
use MetaFox\User\Repositories\Eloquent\UserRelationDataRepository;
use MetaFox\User\Repositories\Eloquent\UserRelationRepository;
use MetaFox\User\Repositories\Eloquent\UserRepository;
use MetaFox\User\Repositories\Eloquent\UserShortcutRepository;
use MetaFox\User\Repositories\Eloquent\UserVerifyAdminRepository;
use MetaFox\User\Repositories\Eloquent\UserVerifyErrorRepository;
use MetaFox\User\Repositories\Eloquent\UserVerifyRepository;
use MetaFox\User\Repositories\ExportProcessRepositoryInterface;
use MetaFox\User\Repositories\InactiveProcessAdminRepositoryInterface;
use MetaFox\User\Repositories\MultiFactorTokenRepositoryInterface;
use MetaFox\User\Repositories\PasswordResetTokenRepositoryInterface;
use MetaFox\User\Repositories\SocialAccountRepositoryInterface;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\User\Repositories\UserBanRepositoryInterface;
use MetaFox\User\Repositories\UserGenderRepositoryInterface;
use MetaFox\User\Repositories\UserPasswordHistoryRepositoryInterface;
use MetaFox\User\Repositories\UserPreferenceRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Repositories\UserProfileRepositoryInterface;
use MetaFox\User\Repositories\UserPromotionRepositoryInterface;
use MetaFox\User\Repositories\UserRelationDataRepositoryInterface;
use MetaFox\User\Repositories\UserRelationRepositoryInterface;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;
use MetaFox\User\Repositories\UserVerifyAdminRepositoryInterface;
use MetaFox\User\Repositories\UserVerifyErrorRepositoryInterface;
use MetaFox\User\Repositories\UserVerifyRepositoryInterface;
use MetaFox\User\Support\Facades\UserAuth;
use MetaFox\User\Support\UserBirthday;
use MetaFox\User\Support\UserBlockedSupport;
use MetaFox\User\Support\UserEntity;
use MetaFox\User\Support\UserPrivacy;
use MetaFox\User\Support\UserValue;
use MetaFox\User\Support\UserVerifySupport;
use MetaFox\User\Support\Verify\Action\ActionServiceManager;

/**
 * Class UserServiceProvider.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackageServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $singletons = [
        'user.verification'                            => UserVerifyRepository::class,
        'UserEntity'                                   => UserEntity::class,
        'UserPrivacy'                                  => UserPrivacy::class,
        'UserValue'                                    => UserValue::class,
        UserVerifyRepositoryInterface::class           => UserVerifyRepository::class,
        AdminLoggedRepositoryInterface::class          => AdminLoggedRepository::class,
        UserAdminRepositoryInterface::class            => UserAdminRepository::class,
        UserRepositoryInterface::class                 => UserRepository::class,
        UserProfileRepositoryInterface::class          => UserProfileRepository::class,
        UserBanRepositoryInterface::class              => UserBanRepository::class,
        SocialAccountRepositoryInterface::class        => SocialAccountRepository::class,
        UserPrivacyRepositoryInterface::class          => UserPrivacyRepository::class,
        CancelReasonAdminRepositoryInterface::class    => CancelReasonAdminRepository::class,
        CancelFeedbackAdminRepositoryInterface::class  => CancelFeedbackAdminRepository::class,
        UserPromotionRepositoryInterface::class        => UserPromotionRepository::class,
        UserRelationRepositoryInterface::class         => UserRelationRepository::class,
        UserShortcutRepositoryInterface::class         => UserShortcutRepository::class,
        MultiFactorTokenRepositoryInterface::class     => MultiFactorTokenRepository::class,
        UserRelationDataRepositoryInterface::class     => UserRelationDataRepository::class,
        UserVerifyErrorRepositoryInterface::class      => UserVerifyErrorRepository::class,
        UserGenderRepositoryInterface::class           => UserGenderRepository::class,
        PasswordResetTokenRepositoryInterface::class   => PasswordResetTokenRepository::class,
        UserBlockedSupportContract::class              => UserBlockedSupport::class,
        UserContract::class                            => \MetaFox\User\Support\User::class,
        ContractsUserAuth::class                       => UserAuth::class,
        PermissionRegistrar::class                     => \MetaFox\User\Support\PermissionRegistrar::class,
        AccountSettingRepositoryInterface::class       => AccountSettingRepository::class,
        UserVerifyAdminRepositoryInterface::class      => UserVerifyAdminRepository::class,
        ActionServiceManagerInterface::class           => ActionServiceManager::class,
        UserVerifySupportContract::class               => UserVerifySupport::class,
        UserPreferenceRepositoryInterface::class       => UserPreferenceRepository::class,
        UserBirthdayContracts::class                   => UserBirthday::class,
        InactiveProcessAdminRepositoryInterface::class => InactiveProcessAdminRepository::class,
        ExportProcessRepositoryInterface::class        => ExportProcessRepository::class,
        UserPasswordHistoryRepositoryInterface::class  => UserPasswordHistoryRepository::class,
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            User::ENTITY_TYPE             => User::class,
            UserShortcut::ENTITY_TYPE     => UserShortcut::class,
            UserBan::ENTITY_TYPE          => UserBan::class,
            UserPrivacyModel::ENTITY_TYPE => UserPrivacyModel::class,
            UserValueModel::ENTITY_TYPE   => UserValueModel::class,
        ]);

        User::observe([UserObserver::class, EloquentModelObserver::class]);
        \MetaFox\User\Models\UserEntity::observe([UserEntityObserver::class]);
        UserBlocked::observe([EloquentModelObserver::class]);
        UserProfile::observe([UserProfileObserver::class, EloquentModelObserver::class]);
        UserRelationHistory::observe([EloquentModelObserver::class]);
        UserBan::observe([UserBanObserver::class]);
        Role::observe([UserRoleObserver::class, EloquentModelObserver::class]);
        UserGender::observe([EloquentModelObserver::class]);
        UserRelation::observe([EloquentModelObserver::class]);
        CancelReason::observe([EloquentModelObserver::class]);
        ExportProcess::observe([EloquentModelObserver::class]);

        \Illuminate\Support\Facades\RateLimiter::for('user_password', function () {
            return Limit::perMinute(5);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Boot facades.
        $this->callAfterResolving('reducer', function ($reducer) {
            $reducer->register([
                \MetaFox\User\Support\LoadMissingPrivacyValues::class,
                \MetaFox\User\Support\LoadMissingUserAndOwner::class,
                \MetaFox\User\Support\LoadMissingIsBlocked::class,
            ]);
        });
    }
}
