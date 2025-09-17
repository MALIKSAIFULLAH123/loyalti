<?php

namespace MetaFox\Platform;

/**
 * Class Screen.
 */
class Screen
{
    public const ACTION_ADD_ITEM = '@app/ADD_ITEM';

    public const ACTION_APPROVE_ITEM = '@app/APPROVE_ITEM';
    public const ACTION_APPROVE_ITEMS = '@app/APPROVE_ITEMS';

    public const ACTION_FEATURE_ITEM = '@app/FEATURE_ITEM';
    public const ACTION_FEATURE_ITEMS = '@app/FEATURE_ITEMS';
    public const ACTION_REMOVE_FEATURE_ITEMS = '@app/REMOVE_FEATURE_ITEMS';

    public const ACTION_PURCHASE_SPONSOR_ITEM = '@app/PURCHASE_SPONSOR_ITEM';

    public const ACTION_SPONSOR_IN_FEED = '@app/SPONSOR_IN_FEED';

    public const ACTION_SPONSOR_ITEM = '@app/SPONSOR_ITEM';
    public const ACTION_REPORT_ITEM = '@app/REPORT_ITEM';

    public const ACTION_EDIT_ITEM = '@app/EDIT_ITEM';
    public const ACTION_DELETE_ITEM = '@app/DELETE_ITEM';
    public const ACTION_DELETE_ITEMS = '@app/DELETE_ITEMS';

    public const ACTION_FILTER_CATEGORY = '@app/FILTER_CATEGORY';

    public const ACTION_FILTER_BY = '@app/FILTER_BY';
    public const ACTION_SORT_BY = '@app/SORT_BY';

    public const ACTION_SHOW_APP_MENU = '@app\/SHOW_APP_MENU';

    public const ACTION_SHARE_ITEM_SUCCESS = '@app/SHARE_ITEM/SUCCESS';
    public const ACTION_EDIT_USER_STATUS = '@feed/EDIT_USER_STATUS';

    public const ACTION_ACCEPT_FRIEND = 'acceptItem';
    public const ACTION_DENY_FRIEND = 'denyItem';
}
