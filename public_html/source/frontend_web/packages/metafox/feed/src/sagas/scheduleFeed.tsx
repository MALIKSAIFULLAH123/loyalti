/**
 * @type: saga
 * name: feed.saga.scheduleFeed
 */

import { takeLatest } from 'redux-saga/effects';
import { APP_FEED, RESOURCE_SCHEDULE } from '@metafox/feed';
import {
  deleteEntity,
  getGlobalContext,
  getResourceAction,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction
} from '@metafox/framework';
import { compactUrl } from '@metafox/utils';

export function* openScheduleFeed(props) {
  const { dialogBackend } = yield* getGlobalContext();

  const { value, setScheduleTime } = props.payload;

  const newValue = yield dialogBackend.present({
    component: 'layout.dialog.FeedSchedule',
    props: {
      value
    }
  });

  setScheduleTime({
    as: 'StatusComposerControlSchedulePlace',
    value: newValue
  });
}
export function* sendNowScheduleFeed(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();
  const { identity } = action.payload;

  if (!identity) return;

  const id = identity.split('.')[3];

  const config = yield* getResourceAction(
    APP_FEED,
    RESOURCE_SCHEDULE,
    'sendNowScheduled'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id }),
      method: config.apiMethod,
      params: config.apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

export function* deleteScheduleFeed(action: ItemLocalAction) {
  const { apiClient } = yield* getGlobalContext();
  const { identity } = action.payload;

  if (!identity) return;

  const id = identity.split('.')[3];

  const config = yield* getResourceAction(
    APP_FEED,
    RESOURCE_SCHEDULE,
    'deleteScheduled'
  );

  if (!config) return;

  try {
    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id }),
      method: config.apiMethod,
      params: config.apiParams
    });

    yield* deleteEntity(identity);

    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [
  takeLatest('statusComposer/openScheduleFeed', openScheduleFeed),
  takeLatest('sendNowScheduled', sendNowScheduleFeed),
  takeLatest('deleteScheduled', deleteScheduleFeed)
];

export default sagas;
