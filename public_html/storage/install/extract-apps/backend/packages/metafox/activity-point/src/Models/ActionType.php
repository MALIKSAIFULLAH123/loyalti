<?php

namespace MetaFox\ActivityPoint\Models;

use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class ActionType.
 *
 * @property int    $id
 * @property string $package_id
 * @property string $name
 * @property string $label_phrase
 * @property string $created_at
 * @property string $updated_at
 */
class ActionType extends Model implements Entity
{
    use HasEntity;

    public const ENTITY_TYPE = 'action_type';

    protected $table = 'apt_action_types';

    public const DEFAULT_ACTION_TYPES = ['create'];

    public const ACTIVITYPOINT_SPEND_POINTS_TYPE                                       = 'activitypoint.spend_points';
    public const ACTIVITYPOINT_RECEIVE_POINTS_FROM_SELLING_ITEMS_TYPE                  = 'activitypoint.receive_points_from_selling_items';
    public const ACTIVITYPOINT_BUY_A_POINT_PACKAGE_TYPE                                = 'activitypoint_package.buy';
    public const ACTIVITYPOINT_RECEIVE_POINTS_TYPE                                     = 'activitypoint.receive_points';
    public const ACTIVITYPOINT_GIFTED_POINTS_TYPE                                      = 'activitypoint.gifted_points';
    public const ACTIVITYPOINT_GIFTING_POINTS_TYPE                                     = 'activitypoint.gifting_points';
    public const ACTIVITYPOINT_CONVERT_POINTS_TO_EMONEY_TYPE                           = 'activitypoint_conversion_request.convert_point_to_emoney';
    public const ACTIVITYPOINT_POINT_REVOCATION_TYPE                                   = 'activitypoint.point_revocation';
}

// end
