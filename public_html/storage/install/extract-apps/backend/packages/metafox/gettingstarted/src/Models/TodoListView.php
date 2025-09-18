<?php

namespace MetaFox\GettingStarted\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class TodoListView extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'todo_list_view';

    protected $table = 'gettingstarted_todo_list_views';

    protected $fillable = [
        'todo_list_id',
        'user_id',
    ];

    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'todo_list_id', 'id');
    }
}
