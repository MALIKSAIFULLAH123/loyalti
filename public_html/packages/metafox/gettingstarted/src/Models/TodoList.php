<?php

namespace MetaFox\GettingStarted\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class Blog.
 *
 * @mixin Builder
 * @property int         $id
 * @property string      $title
 * @property string|null $title_var
 * @property string      $created_at
 * @property string      $updated_at
 * @property int         $ordering
 * @property mixed       $descriptions
 * @property mixed       $description
 * @property string      $resolution
 * @property Collection  $images
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TodoList extends Model implements
    Entity
{
    use HasEntity;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'todo_list';

    protected $table = 'gettingstarted_todo_lists';

    protected $fillable = ['title', 'ordering', 'resolution'];

    protected $translatableAttributes = ['title'];

    public function descriptions(): HasMany
    {
        return $this->hasMany(TodoListText::class, 'todo_list_id', 'id');
    }

    public function masterDescription(): HasOne
    {
        return $this->hasOne(TodoListText::class, 'todo_list_id', 'id')->where('locale', 'en');
    }

    public function description(): HasOne
    {
        $locale = app()->getLocale() ?: 'en';

        return $this->hasOne(TodoListText::class, 'todo_list_id', 'id')->where('locale', $locale);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TodoListImage::class, 'todo_list_id', 'id')->orderBy('ordering');
    }

    public function getTitleAttribute($value): string
    {
        $name = is_string($value) ? __p($value) : $value;

        return  $value === $name ? __translation_wrapper(__translation_prefix($name, $name)) : $name;
    }

    public function getTitleVarAttribute(): ?string
    {
        return $this->attributes['title'] ?? null;
    }

    public function getLabelAttribute($value): ?string
    {
        return $value ? __p($value) : $this->title;
    }

    public function getAdminEditUrlAttribute()
    {
        return sprintf('/getting-started/todo-list/edit/%s', $this->entityId());
    }
}
