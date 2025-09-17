<?php

namespace MetaFox\GettingStarted\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

class TodoListText extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'todo_list_text';

    public $timestamps = false;

    protected $table = 'gettingstarted_todo_list_texts';

    protected $fillable = [
        'todo_list_id',
        'text',
        'text_parsed',
        'locale',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'todo_list_id', 'id');
    }
}
