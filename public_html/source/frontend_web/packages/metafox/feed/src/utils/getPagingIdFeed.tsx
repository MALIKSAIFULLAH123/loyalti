import { compactUrl } from '@metafox/utils';
import { PAGINGID_FEED, PAGINGID_USER_FEED } from '../constant';

// sort ='recent' should load from setting for default
export const getPagingIdFeed = (id?: string, sort: string = 'recent') => {
  return id
    ? compactUrl(PAGINGID_USER_FEED, { id, sort })
    : compactUrl(PAGINGID_FEED, { sort });
};
