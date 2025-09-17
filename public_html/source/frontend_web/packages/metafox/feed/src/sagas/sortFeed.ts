/**
 * @type: saga
 * name: saga.feed.sortFeed
 */

import {
  getGlobalContext,
  getResourceConfig,
  getSession,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import {
  APP_FEED,
  RESOURCE_FEED,
  UPDATE_SORT_VALUE_RESOURCE
} from '../constant';
import { compactData } from '@metafox/utils';

function* sortFeed(
  action: ItemLocalAction<{
    sort: string;
    module_name: string;
    resource_name: string;
  }>
) {
  const { getPageParams, apiClient } = yield* getGlobalContext();
  const { user, loggedIn } = yield* getSession();
  const pageParams = getPageParams();

  const config = yield* getResourceConfig(
    APP_FEED,
    RESOURCE_FEED,
    'updateSort'
  );

  if (!user || !loggedIn) return;

  const { sort, module_name, resource_name } = action.payload || {};

  try {
    const { sort_feed_preferences: sortPreferences } = user || {};

    const sort_feed_preferences = {
      ...sortPreferences,
      [module_name]: {
        ...sortPreferences?.[module_name],
        [resource_name]: sort
      }
    };

    yield* patchEntity(user._identity, { sort_feed_preferences });

    if (!config.apiUrl) return;

    yield apiClient.request({
      method: config?.apiMethod,
      url: config?.apiUrl,
      data: compactData(config?.apiParams, { id: pageParams?.id, sort })
    });
  } catch (error) {
    yield;
  }
}

const sagas = [takeEvery(UPDATE_SORT_VALUE_RESOURCE, sortFeed)];

export default sagas;
