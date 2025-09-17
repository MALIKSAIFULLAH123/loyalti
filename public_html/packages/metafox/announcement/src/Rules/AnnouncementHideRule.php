<?php

namespace MetaFox\Announcement\Rules;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\Hidden;
use MetaFox\Platform\Contracts\User;

/**
 * Class AnnouncementHideRule.
 * @ignore
 * @codeCoverageIgnore
 */
class AnnouncementHideRule implements RuleContract
{
    protected string $message;

    public function __construct(protected User $user)
    {
    }

    /**
     * @param  string                  $attribute
     * @param  mixed                   $value
     * @return bool
     * @throws AuthenticationException
     */
    public function passes($attribute, $value): bool
    {
        if (!is_numeric($value)) {
            $this->setMessage(__p('announcement::validation.the_field_is_not_numeric', [
                'field' => $attribute,
            ]));

            return false;
        }

        $user      = $this->user;
        $data      = ['announcement_id' => $value];
        $validator = Validator::make($data, [
            'announcement_id' => [
                'numeric',
                Rule::exists(Announcement::class, 'id'),
                Rule::unique(Hidden::class, 'announcement_id')->where('user_id', $user->entityId()),
            ],
        ]);

        if ($validator->fails()) {
            $this->setMessage($validator->errors()->toJson());

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
