<?php

namespace MetaFox\User\Observers;

use Illuminate\Support\Carbon;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Support\Facades\UserEntity;

class UserProfileObserver
{
    public function creating(UserProfile $userProfile): void
    {
        $this->setBirthdayDOY($userProfile);
        $this->setBirthdayMonth($userProfile);
    }

    public function created(UserProfile $userProfile): void
    {
        $this->updateUserProfile($userProfile);

        app('events')->dispatch('core.privacy_stream.create', [$userProfile]);
    }

    public function updating(UserProfile $userProfile): void
    {
        if ($userProfile->isDirty('birthday')) {
            $this->setBirthdayDOY($userProfile);
            $this->setBirthdayMonth($userProfile);
        }
    }

    public function updated(UserProfile $userProfile): void
    {
        $this->updateUserProfile($userProfile);
    }

    private function updateUserProfile(UserProfile $userProfile): void
    {
        // Get full data.
        $userProfile->refresh();

        UserEntity::updateEntity($userProfile->entityId(), [
            'avatar_id'      => $userProfile->avatar_id,
            'avatar_file_id' => $userProfile->avatar_file_id,
            'gender'         => $userProfile->gender_id ?? 0,
        ]);
    }

    /**
     * @param UserProfile $userProfile
     *
     * @return void
     */
    private function setBirthdayDOY(UserProfile $userProfile): void
    {
        if (null != $userProfile->birthday) {
            $birthdayParse = Carbon::parse($userProfile->birthday);
            $leapYearNumber = getDayOfLeapYearNumber($birthdayParse);

            $userProfile->birthday_doy = $birthdayParse->dayOfYear + $leapYearNumber; //convert to leap year
        }

        if (null == $userProfile->birthday) {
            $userProfile->birthday_doy = null; //convert to leap year
        }
    }

    /**
     * @param UserProfile $userProfile
     *
     * @return void
     */
    private function setBirthdayMonth(UserProfile $userProfile): void
    {
        if (null != $userProfile->birthday) {
            $birthdayParse = Carbon::parse($userProfile->birthday);

            $userProfile->birthday_month = $birthdayParse->month;
        }
        
        if (null == $userProfile->birthday) {
            $userProfile->birthday_month = null; //convert to leap year
        }
    }
}
