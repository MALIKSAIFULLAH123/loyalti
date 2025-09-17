<?php

namespace MetaFox\Form;

use MetaFox\Platform\MetaFoxConstant;

/**
 * Class MetaFoxForm.
 */
class Constants
{
    public const ALERT               = 'Alert';
    public const TEXT                = 'Text';
    public const RAW_FILE            = 'RawFile';
    public const EMAIL               = 'Email';
    public const PASSWORD            = 'Password';
    public const CHOICE              = 'Choice';
    public const RADIO               = 'Radio';
    public const RADIO_GROUP         = 'RadioGroup';
    public const VALIDATE_TEXT       = 'ValidateText';
    public const FILE                = 'File';
    public const ATTACHMENT          = 'Attachment';
    public const CUSTOM_GENDERS      = 'CustomGenders';
    public const BIRTHDAY            = 'Birthday';
    public const HIDDEN              = 'Hidden';
    public const PRIVACY             = 'Privacy';
    public const LOCATION            = 'Location';
    public const MULTIPLE_LOCATION   = 'MultipleLocation';
    public const RELATIONSHIP_PICKER = 'RelationshipPicker';
    public const RICH_TEXT_EDITOR    = 'Editor';
    public const FRIEND_PICKER       = 'FriendPicker';
    public const CHIP                = 'Chip';
    public const SUBMIT              = 'Submit';
    public const SUBMIT_ACTION       = 'SubmitAction';
    public const CANCEL_BUTTON       = 'Cancel';
    public const ADD_TEXT_STYLE      = 'AddTextStyle';
    public const SELECT_BACKGROUND   = 'SelectBackground';
    public const FIELD_DIVIDER       = 'Divider';
    public const FIELD_TYPE_CATEGORY = 'TypeCategory';
    public const COUNTRY_STATE       = 'CountryState';
    public const AUTOCOMPLETE        = 'Autocomplete';
    public const SINGLE_UPDATE_INPUT = 'SingleUpdateInputField';
    public const DATETIME            = 'Datetime';
    public const DROPDOWN            = 'Dropdown';
    public const DATE                = 'Date';
    public const CUSTOM_BUTTON       = 'CustomButton';
    public const LINK_BUTTON         = 'LinkButton';
    public const HTML_LINK           = 'HtmlLink';
    public const QR_CODE             = 'QrCode';
    public const AUTH_QR_CODE        = 'AuthenticatorQrCode';
    public const NUMBER_CODE         = 'NumberCode';
    public const RESTORE_DEFAULT     = 'RestoreDefault';
    public const DATE_BASIC          = 'DateBasic';

    public const MIN_DATE       = '1900-1-1';
    public const MAX_DATE       = '2017-12-31';
    public const CONTAINER      = 'Container';
    public const SWITCH_FIELD   = 'Switch';
    public const ICON_TOGGLE    = 'IconToggle';
    public const TEXT_AREA      = 'Textarea';
    public const COMPOSER_INPUT = 'ComposerInput';
    public const MUX_PLAYER     = 'MuxPlayer';

    public const COPY_TEXT   = 'CopyText';
    public const DESCRIPTION = 'Description';

    public const FREE_OPTIONS = 'FreeOptions';

    public const ACTION_ADMINCP_BATCH_ITEM = '@admin/batchItem';

    public const ACTION_BATCH_ITEM     = 'row/batchItem';
    public const ACTION_BATCH_ACTIVE   = 'row/batchActive';
    public const ACTION_BATCH_INACTIVE = 'row/batchInactive';
    public const ACTION_BATCH_EDIT     = 'row/batchEdit';
    public const ACTION_ROW_EDIT       = 'row/edit';
    public const ACTION_ROW_PATCH      = 'row/edit';
    public const ACTION_ROW_LINK       = 'row/redirect';
    public const ACTION_ROW_REQUEST    = 'row/request';
    public const ACTION_ROW_DOWNLOAD   = 'row/download';
    public const ACTION_ROW_DELETE     = 'row/remove';
    public const ACTION_ROW_ACTIVE     = 'row/active';
    public const ACTION_BATCH_DELETE   = 'row/batchRemove';
    public const ACTION_SHOW_INFO      = 'row/showInfo';

    public const GRID_CELL_SWITCH_ACTIVE         = 'SwitchActiveCell';
    public const GRID_CELL_OPTION                = 'OptionCell';
    public const GRID_CELL_DATETIME              = 'DatetimeCell';
    public const GRID_CELL_FROM_NOW              = 'FromNowCell';
    public const GRID_CELL_AVATAR                = 'AvatarCell';
    public const GRID_CELL_NUMBER                = 'NumberCell';
    public const ACTION_ROW_ADD                  = 'row/add';
    public const METHOD_POST                     = 'POST';
    public const METHOD_GET                      = 'GET';
    public const METHOD_PUT                      = 'PUT';
    public const METHOD_DELETE                   = 'DELETE';
    public const METHOD_PATCH                    = 'PATCH';
    public const FIELD_TAG                       = 'Tags';
    public const FORM_SUBMIT_ACTION_SEARCH       = '@form/search/SUBMIT';
    public const FORM_ADMIN_SUBMIT_ACTION_SEARCH = '@formAdmin/search/SUBMIT';
    public const FORM_SUBMIT_ACTION_SAVE         = '@form/search/SUBMIT';
    public const CHECKBOX_FIELD                  = 'Checkbox';
    public const CHECKBOX_GROUP                  = 'CheckboxGroup';
    public const SEARCH_BOX_FIELD                = 'SearchBox';
    public const SEARCH_TEXT_BOX_FIELD           = 'SearchTextBox';
    public const ICON_BUTTON_FIELD               = 'IconButton';
    public const CAPTCHA_FIELD                   = 'Captcha';
    public const IMAGE_CAPTCHA_FIELD             = 'ImageCaptcha';
    public const HIDDEN_IMAGE_CAPTCHA_FIELD      = 'HiddenImageCaptcha';
    public const DIALOG_HEADER                   = 'DialogHeader';
    public const BUTTON                          = 'Button';
    public const SINGLE_PHOTO                    = 'SinglePhotoFile';
    public const FILTER_CATEGORY                 = 'FilterCategory';
    public const SIMPLE_CATEGORY                 = 'SimpleCategory';

    public const COMPONENT_TEXTAREA           = 'Textarea';
    public const COMPONENT_TEXT               = 'Text';
    public const COMPONENT_TYPE_CATEGORY      = 'TypeCategory';
    public const COMPONENT_SELECT             = 'Select';
    public const COMPONENT_TABS               = 'Tabs';
    public const COMPONENT_TYPOGRAPHY         = 'Typo';
    public const COMPONENT_DYNAMIC_TYPOGRAPHY = 'DynamicTypo';

    public const COMPONENT_CLICKABLE    = 'Clickable';
    public const COMPONENT_SORTABLE     = 'Sortable';
    public const COMPONENT_PRICE        = 'Price';
    public const COMPONENT_COLOR        = 'Color';
    public const COMPONENT_COLOR_PICKER = 'ColorPicker';
    public const COMPONENT_SLUG         = 'Slug';
    public const COMPONENT_CLEAR_SEARCH = 'ClearSearch';
    public const COMPONENT_RESET_SEARCH = 'ResetSearch';
    public const COMPONENT_FILTER_PRICE = 'FilterPrice';
    public const COMPONENT_SLIDER       = 'Slider';
    public const COMPONENT_RANGE        = 'Range';

    public const COMPONENT_VIEW_MORE         = 'ViewMore';
    public const COMPONENT_CLEAR_SEARCH_FORM = 'ClearSearchForm';

    public const COMPONENT_CUSTOM_FIELD  = 'CustomField';

    public const COMPONENT_WITHDRAWAL    = 'Withdrawal';
    public const FORM_ACTION_REDIRECT_TO = '@redirectTo';

    public const FIELD_WIDTH_ONE_THIRD = '33%';
    public const FIELD_WIDTH_ONE       = '100%';
    public const FIELD_WIDTH_HALF      = '50%';
    public const FIELD_WIDTH_QUARTER   = '25%';

    /**
     * @return array<int, string>
     */
    public static function getRelations(): array
    {
        return [
            MetaFoxConstant::RELATION_UNKNOWN                => 'Unknown status',
            MetaFoxConstant::RELATION_SINGLE                 => 'Single',
            MetaFoxConstant::RELATION_ENGAGED                => 'Engaged',
            MetaFoxConstant::RELATION_MARRIED                => 'Married',
            MetaFoxConstant::RELATION_COMPLICATED            => "It's complicated",
            MetaFoxConstant::RELATION_IN_A_OPEN_RELATIONSHIP => 'In an open relationship',
            MetaFoxConstant::RELATION_WIDOWED                => 'Widowed',
            MetaFoxConstant::RELATION_SEPARATED              => 'Separated',
            MetaFoxConstant::RELATION_DIVORCED               => 'Divorced',
            MetaFoxConstant::RELATION_IN_A_RELATIONSHIP      => 'In a relationship',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getGenders(): array
    {
        return [
            MetaFoxConstant::GENDER_MALE   => 'Male',
            MetaFoxConstant::GENDER_FEMALE => 'Female',
            MetaFoxConstant::GENDER_OTHERS => 'Others',
        ];
    }

    /**
     * @return array<int>
     */
    public static function getAllowGenders(): array
    {
        return [
            MetaFoxConstant::GENDER_MALE,
            MetaFoxConstant::GENDER_FEMALE,
            MetaFoxConstant::GENDER_OTHERS,
        ];
    }
}
