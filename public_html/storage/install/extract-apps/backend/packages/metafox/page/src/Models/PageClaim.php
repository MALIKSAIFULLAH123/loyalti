<?php

namespace MetaFox\Page\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Page\Database\Factories\PageClaimFactory;
use MetaFox\Page\Notifications\ClaimNotification;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasUrl;
use MetaFox\Platform\Contracts\IsNotifyInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasUserMorph;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserSupport;

/**
 * Class PageClaim.
 * @property        int              $status_id
 * @property        int              $page_id
 * @property        Page             $page
 * @property        string|null      $message
 * @property        string           $updated_at
 * @property        string           $created_at
 * @method   static PageClaimFactory factory(...$parameters)
 */
class PageClaim extends Model implements Entity, IsNotifyInterface, HasUrl
{
    use HasEntity;
    use HasUserMorph;
    use HasFactory;

    public const ENTITY_TYPE    = 'page_claim';

    protected $table = 'page_claims';

    protected $fillable = [
        'status_id',
        'page_id',
        'user_id',
        'user_type',
        'message',
    ];

    protected static function newFactory(): PageClaimFactory
    {
        return PageClaimFactory::new();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'id')->withTrashed();
    }

    public function toNotification(): ?array
    {
        $adminIds = Settings::get('page.admin_in_charge_of_page_claims', []);

        $admins = User::query()->whereIn('id', $adminIds)->get();
        $users  = UserSupport::getUsersByRoleId(UserRole::SUPER_ADMIN_USER);

        foreach ($users as $user) {
            /* @var User $user */
            $admins[] = $user;
        }

        return [$admins, new ClaimNotification($this)];
    }

    public function toLink(): ?string
    {
        if (!$this->page instanceof Page) {
            return null;
        }

        return $this->page->toLink();
    }

    public function toUrl(): ?string
    {
        if (!$this->page instanceof Page) {
            return null;
        }

        return $this->page->toUrl();
    }

    public function toRouter(): ?string
    {
        if (!$this->page instanceof Page) {
            return null;
        }

        return $this->page->toRouter();
    }

    public function isPending(): bool
    {
        return $this->status_id == PageClaimSupport::STATUS_PENDING;
    }

    public function isDenied(): bool
    {
        return $this->status_id == PageClaimSupport::STATUS_DENY;
    }

    public function isCancelled(): bool
    {
        return $this->status_id == PageClaimSupport::STATUS_CANCEL;
    }

    public function isGranted(): bool
    {
        return $this->status_id == PageClaimSupport::STATUS_APPROVE;
    }
}
