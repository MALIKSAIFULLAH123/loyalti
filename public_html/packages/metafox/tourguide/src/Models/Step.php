<?php

namespace MetaFox\TourGuide\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Step.
 *
 * @property int       $id
 * @property int       $tour_guide_id
 * @property TourGuide $tourGuide
 * @property string    $title_var
 * @property string    $title
 * @property string    $desc_var
 * @property string    $desc
 * @property int       $ordering
 * @property int       $delay
 * @property string    $background_color
 * @property string    $font_color
 * @property string    $element
 * @property bool      $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class Step extends Model implements Entity
{
    use HasEntity;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'tour_guide_step';

    protected $table = 'tour_guide_steps';

    public array $translatableAttributes = [
        'title_var',
        'desc_var',
    ];

    /** @var string[] */
    protected $fillable = [
        'tour_guide_id',
        'title_var',
        'desc_var',
        'ordering',
        'delay',
        'background_color',
        'font_color',
        'element',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tourGuide(): BelongsTo
    {
        return $this->belongsTo(TourGuide::class, 'tour_guide_id', 'id');
    }

    public function getTitleAttribute(): string
    {
        if (!is_string($this->title_var)) {
            return '';
        }

        return htmlspecialchars_decode(__p($this->title_var));
    }

    public function getDescAttribute(): string
    {
        if (!is_string($this->desc_var)) {
            return '';
        }

        return htmlspecialchars_decode(__p($this->desc_var));
    }
}
