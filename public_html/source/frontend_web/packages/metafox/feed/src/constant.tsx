export const USERS_TAB = 0;

export const PAGES_TAB = 1;

export const GROUPS_TAB = 2;

// FEED HIDDEN CASE

export const HIDE_ITEM = 0;

export const HIDE_ALL_USER = 1;

export const HIDE_ALL_OWNER = 2;

export const HIDE_ALL_SHARED_USER = 3;

export const HIDE_ALL_SHARED_OWNER = 4;

export const SNOOZE_USER = 5;

export const SNOOZE_OWNER = 6;

export const SNOOZE_SHARED_USER = 7;

export const SNOOZE_SHARED_OWNER = 8;

export const APP_FEED = 'feed';

export const THROTTLE_FETCH_LINK = 300;

export const REGEX_LENGTH_TEXT = /\[(\w+)=(\d+)\](.*?)\[\/(\w+)\]/gm;

export const RESOURCE_SCHEDULE = 'activity_schedule';
export const RESOURCE_SCHEDULE_EMBED = 'scheduled_embed';

export const RESOURCE_ACTIVITY = 'activity';

export const RESOURCE_FEED = 'feed';

export const PAGINGID_FEED = '/feed?sort=:sort';

export const PAGINGID_USER_FEED = '/feed?user_id=:id&sort=:sort';

export const PAGING_REFRESH_ACTION = 'feed/paging/refresh';

export const UPDATE_SORT_VALUE_RESOURCE = 'feed/updateValue/sortFeed';

export const RESOURCE_SNOOZE = 'activity_snooze';
