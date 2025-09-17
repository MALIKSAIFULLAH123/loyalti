<?php

namespace MetaFox\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;

/**
 * Class TranslationGateway.
 *
 * @property int    $id
 * @property int    $entity_id
 * @property string $entity_type
 * @property string $language_id
 * @property string $text
 */
class TranslationText extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'translation_text';
    public $timestamps = false;
    protected $table = 'translation_text';
    protected $fillable = [
        'entity_id',
        'entity_type',
        'language_id',
        'text',
    ];
}
