<?php

namespace Foxexpert\Sevent\Support\Browse\Scopes\Invoice;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

/**
 * Class ViewScope.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class ViewScope extends BaseScope
{
    public const VIEW_DEFAULT = self::VIEW_BOUGHT;
    public const VIEW_BOUGHT  = 'bought';
    public const VIEW_SOLD    = 'sold';

    /**
     * @return array<int, string>
     */
    public static function getAllowView(): array
    {
        return [
            self::VIEW_BOUGHT,
            self::VIEW_SOLD,
        ];
    }

    /**
     * @var string
     */
    private string $view = self::VIEW_DEFAULT;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @return User
     */
    public function getUserContext(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return ViewScope
     */
    public function setUserContext(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return ViewScope
     */
    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model)
    {
        $view    = $this->getView();
        $context = $this->getUserContext();
        $userId = $context->entityId();
        switch ($view) {
            case self::VIEW_BOUGHT:
                $builder->where([
                    'user_id'   => $context->entityId(),
                    'user_type' => $context->entityType(),
                ]);
                break;
            case self::VIEW_SOLD:
                $builder->join('sevents', function($join) use ($userId) {
                    $join->on('sevent_invoices.sevent_id', '=', 'sevents.id')
                         ->where('sevents.user_id', '=', $userId); 
                });
                break;
        }
    }
}
