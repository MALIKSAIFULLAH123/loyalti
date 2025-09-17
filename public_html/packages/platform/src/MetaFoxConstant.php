<?php

namespace MetaFox\Platform;

/**
 * Class MetaFoxConstant.
 */
class MetaFoxConstant
{
    /* wrap backend in the directory.*/
    public const BACKEND_WRAP_NAME = 'backend';
    /* wrap frontend in the directory.*/
    public const FRONTEND_WRAP_NAME = 'frontend';

    public const VERSION       = '5.1.19';
    public const PRODUCT_BUILD = '20';

    public const RIGHT  = 'right';
    public const LEFT   = 'left';
    public const CENTER = 'center';

    public const GENDER_MALE   = 1;
    public const GENDER_FEMALE = 2;
    public const GENDER_OTHERS = 127;

    public const RELATION_UNKNOWN                = 1;
    public const RELATION_SINGLE                 = 2;
    public const RELATION_ENGAGED                = 3;
    public const RELATION_MARRIED                = 4;
    public const RELATION_COMPLICATED            = 5;
    public const RELATION_IN_A_OPEN_RELATIONSHIP = 6;
    public const RELATION_WIDOWED                = 7;
    public const RELATION_SEPARATED              = 8;
    public const RELATION_DIVORCED               = 9;
    public const RELATION_IN_A_RELATIONSHIP      = 10;
    public const DEFAULT_LIMIT_FRIEND_REQUEST    = 10;

    public const GUEST_USER_ID = 0;

    public const IS_PUBLIC     = 1;
    public const IS_NOT_PUBLIC = 0;

    public const SORT_DESC = 'desc';
    public const SORT_ASC  = 'asc';

    public const CHARACTER_LIMIT = 155;
    public const SEPARATION_PERM = ':';

    public const CACHE_TIME = 3000;

    public const TIME_Y_M_D             = 'Y-m-d H:i:s';
    public const DISPLAY_FORMAT_TIME    = 'DD/MM/YYYY';
    public const DISPLAY_FORMAT_TIME_24 = 'DD/MM/YYYY - HH:mm';
    public const DISPLAY_FORMAT_TIME_12 = 'DD/MM/YYYY - hh:mm A';

    public const IS_ACTIVE   = 1;
    public const IS_INACTIVE = 0;

    public const PACKAGE_PRIORITY_DEFAULT             = 100;
    public const DEFAULT_MIN_TITLE_LENGTH             = 1;
    public const DEFAULT_MAX_TITLE_LENGTH             = 255;
    public const DEFAULT_MAX_SEO_TITLE_LENGTH         = 60;
    public const DEFAULT_MAX_CATEGORY_TITLE_LENGTH    = 255;
    public const DEFAULT_MAX_SHORT_DESCRIPTION_LENGTH = 255;

    public const EMPTY_STRING               = '';
    public const BLANK_SPACE                = ' ';
    public const NESTED_ARRAY_SEPARATOR     = '=>';
    public const HTML_ENTITIES_GREATER_THAN = '&gt;';
    public const HTML_ENTITIES_LESSER_THAN  = '&lt;';

    /**
     * |--------------------------------------------------------------------------
     * | Application Regex
     * |--------------------------------------------------------------------------
     * | For global regex declaration
     * |.
     */
    public const RESOURCE_IDENTIFIER_REGEX = '^[a-z][a-z0-9_]*[a-z0-9]$';

    public const SLUGIFY_FILTERS         = ['/', ' ', '!', '@', '#', '$', '%', '^', '&', '*', '?', ',', '.', ':', ';', '`', '~', '(', ')', '{', '}', '[', ']', '=', '+', '\\', '|', '"', '<', '>'];
    public const SLUGIFY_FILTERS_REPLACE = '-';
    public const HEX_COLOR_REGEX         = '^#(?:[0-9a-fA-F]{3}){1,2}$';
    public const SLUGIFY_REGEX           = '^[a-z0-9]+(?:-[a-z0-9]+)*$';
    public const EMAIL_REGEX             = '^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$';

    public const IP_ADDRESS_V4_REGEX          = '(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?|0)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?|0)){3}';
    public const IP_ADDRESS_V6_REGEX          = '(([0-9a-fA-F]{1,4}:){7}([0-9a-fA-F]{1,4})|(([0-9a-fA-F]{1,4}:){1,7}:)|(([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4})|(([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2})|(([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3})|(([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4})|(([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5})|([0-9a-fA-F]{1,4}:)((:[0-9a-fA-F]{1,4}){1,6})|(:((:[0-9a-fA-F]{1,4}){1,7}|:))|(::))';
    public const IP_ADDRESS_V4_REGEX_WILDCARD = '(\*|25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?|0)(\.(\*|25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?|0)){3}';
    public const IP_ADDRESS_V6_REGEX_WILDCARD = '([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*):([0-9a-fA-F]{1,4}|\*)';

    /**
     * @deprecated Remove in 5.2.0
     */
    public const PHONE_NUMBER_REGEX = '(\+\d{1,4}([-.\s]?)?(?:[(](\d{1,3})[)][-.\s]?)?(\d{1,4})[-.\s]?(\d{1,4})[-.\s]?(\d{1,9})$)|(^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$)|(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*(\d{1,2}))$';

    public const CONTENT_PARSING_REGEX   = '/\[%s=(\d+)\](.+?)\[\/%s\]/u';
    public const PAYMENT_STATUS_SUCCESS  = 'success';
    public const PAYMENT_STATUS_CANCELED = 'canceled';
    public const PAYMENT_STATUS_EXPIRED  = 'expired';
    public const PAYMENT_STATUS_PENDING  = 'pending';

    public const DEFAULT_CURRENCY_ID     = 'USD';
    public const DEFAULT_CURRENCY_SYMBOL = '$';

    public const ITEM_STATUS_APPROVED = 'approved';
    public const ITEM_STATUS_PENDING  = 'pending';
    public const ITEM_STATUS_DENIED   = 'denied';
    public const ITEM_STATUS_REMOVED  = 'removed';

    public const STATUS_PENDING_APPROVAL                  = 'pending_approval';
    public const STATUS_PENDING_VERIFICATION_EMAIL        = 'pending_verification_email';
    public const STATUS_PENDING_VERIFICATION_PHONE_NUMBER = 'pending_verification_phone_number';
    public const STATUS_APPROVED                          = 'approved';
    public const STATUS_NOT_APPROVED                      = 'not_approved';
    public const STATUS_ONLINE                            = 'online';
    public const STATUS_FEATURED                          = 'featured';

    public const VIEW_5_NEAREST  = 5;
    public const VIEW_10_NEAREST = 10;
    public const VIEW_15_NEAREST = 15;
    public const VIEW_20_NEAREST = 20;

    public const RESOLUTION_WEB    = 'web';
    public const RESOLUTION_MOBILE = 'mobile';
    public const RESOLUTION_ADMIN  = 'admin';

    public const TWO_WAY_FRIENDSHIPS = 2;
    public const ONE_WAY_FRIENDSHIPS = 1;

    public const MAX_CATEGORY_LEVEL = 3;

    /**@deprecated v5.2 */
    public const FILE_CREATE_STATUS = 'create';

    public const FILE_NEW_STATUS    = 'new';
    public const FILE_UPDATE_STATUS = 'update';
    public const FILE_REMOVE_STATUS = 'remove';

    public const PRIVACY_ICON = 'privacy_icon';

    public const MAX_NUMBER_OF_FILES = 5;

    public const VIEW_FOLLOWING = 'following';
    public const VIEW_FOLLOWER  = 'follower';

    public const DEFAULT_API_VERSION          = 'v1';
    public const VIDEO_SERVICE_TYPE           = 'video-service';
    public const STORY_SERVICE_TYPE           = 'story-service';
    public const LIVESTREAMING_SERVICE_TYPE   = 'livestreaming-service';
    public const VIDEO_SERVICE_DEFAULT        = 'ffmpeg';
    public const TYPE_MULTISTEP_FORM_NEXT     = 'multiStepForm/next';
    public const TYPE_MULTISTEP_FORM_DONE     = 'multiStepForm/done';
    public const TYPE_MULTISTEP_FORM_PREVIOUS = 'multiStepForm/previous';
    public const TYPE_REQUEST_ACTION          = '@requestAction';
    public const TYPE_FORM_SCHEMA             = 'formSchema';
    public const LAYOUT_AS_LIST               = '1';
    public const LAYOUT_AS_GRID               = '2';
    public const TIMEFRAME_FOREVER            = 'forever';
    public const TIMEFRAME_DAILY              = 'daily';
    public const TIMEFRAME_MONTHLY            = 'monthly';

    public const OPERATION_AGGREGATE_FUNCTION_SUM = 'sum';
    public const OPERATION_AGGREGATE_FUNCTION_MAX = 'max';
    public const OPERATION_AGGREGATE_FUNCTION_MIN = 'min';
}
