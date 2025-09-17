<?php

namespace MetaFox\GettingStarted\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Platform\Traits\Eloquent\Model\HasThumbnailTrait;

/**
 * Class TodoListImage.
 *
 * @property int $id
 * @property int $todo_list_id
 * @property int $image_file_id
 * @property int $ordering
 */
class TodoListImage extends Model implements
    Entity,
    HasThumbnail
{
    use HasEntity;
    use HasThumbnailTrait;

    public const ENTITY_TYPE = 'todo_list_image';

    protected $table = 'gettingstarted_todo_list_images';

    public $timestamps = false;

    protected $fillable = [
        'todo_list_id',
        'image_file_id',
        'ordering',
    ];

    public function getThumbnail(): ?string
    {
        return $this->image_file_id;
    }

    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class, 'todo_list_id', 'id');
    }
}
