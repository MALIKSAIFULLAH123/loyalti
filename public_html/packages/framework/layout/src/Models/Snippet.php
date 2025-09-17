<?php

namespace MetaFox\Layout\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Layout\Database\Factories\SnippetFactory;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Snippet.
 *
 * @property int     $id
 * @method   static  SnippetFactory factory(...$parameters)
 * @property ?string $theme
 * @property string  $name
 * @property string  $type
 * @property array   $data
 * @property int     $revision_id
 * @property string  $created_at
 * @property string  $updated_at
 * @property bool    $is_active
 */
class Snippet extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'layout_snippet';

    protected $table = 'layout_snippets';

    /** @var string[] */
    protected $fillable = [
        'theme',
        'type',
        'name',
        'variant',
        'snippet',
        'data',
        'revision_id',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return SnippetFactory
     */
    protected static function newFactory()
    {
        return SnippetFactory::new();
    }

    protected static function booted()
    {
        static::deleted(function (self $snippet) {
            Revision::query()->where('snippet_id', '=', $snippet->id)->delete();
        });
    }
}

// end
