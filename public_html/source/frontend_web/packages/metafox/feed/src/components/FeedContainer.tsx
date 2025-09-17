/**
 * @type: ui
 * name: feed.ui.FeedContainer
 * bundle: web
 * chunkName: feed
 */

import React from 'react';
import { useGlobal } from '@metafox/framework';
import { APP_FEED, UPDATE_SORT_VALUE_RESOURCE } from '../constant';
import { getPagingIdFeed } from '../utils';

const APP_USER = 'user';
const RESOURCE_HOME_FEED = 'home';

export const getAppResourceSort = pageParams => {
  const { module_name, resource_name } = pageParams || {};

  if (module_name === APP_FEED) return [APP_USER, RESOURCE_HOME_FEED];

  return [module_name, resource_name];
};

export const useGetSortFeed = () => {
  const { getSetting, usePageParams, useSession } = useGlobal();
  const SORT_SETTING: string = getSetting('activity.feed.sort_default');
  const pageParams = usePageParams();
  const { user } = useSession();

  const { sort_feed_preferences: sortPreferences } = user || {};
  const [module_name, resource_name] = getAppResourceSort(pageParams);

  const sort = React.useMemo(() => {
    return sortPreferences?.[module_name]?.[resource_name] || SORT_SETTING;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sortPreferences, module_name, resource_name]);

  return [sort, module_name, resource_name];
};

function FeedContainer(props) {
  const { jsxBackend, dispatch, usePageParams, useLoggedIn } = useGlobal();
  const pageParams = usePageParams();
  const isLogged = useLoggedIn();

  const [sortFeed, module_name, resource_name] = useGetSortFeed();
  const sortRef = React.useRef(sortFeed);
  const sort = sortRef.current;
  const pagingId = props?.pagingId || getPagingIdFeed(pageParams?.id, sort);

  const ListView = jsxBackend.get('feed.ui.Listing');
  const SortActivityFeed = jsxBackend.get('ui.SortActivityFeed');

  const onChangeSort = values => {
    const sortData = values?.sort;
    sortRef.current = sortData;
    dispatch({
      type: UPDATE_SORT_VALUE_RESOURCE,
      payload: {
        module_name,
        resource_name,
        sort: sortData
      }
    });
  };

  return (
    <>
      {SortActivityFeed && isLogged ? (
        <SortActivityFeed onSubmit={onChangeSort} sort={sort} />
      ) : null}
      <ListView {...props} pagingId={pagingId} sort={sort} />
    </>
  );
}

export default FeedContainer;
