/**
 * @type: saga
 * name: livestreaming.saga.updatedItem
 */

import { LocalAction, viewItem, getGlobalContext } from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_LIVESTREAM, RESOURCE_LIVE_VIDEO } from '../constants';

function* updatedItem({ payload: { id } }: LocalAction<{ id: string }>) {
  const { getPageParams, navigate } = yield* getGlobalContext();
  const { id: pageParamsId } = getPageParams();

  if (pageParamsId) {
    yield* viewItem(APP_LIVESTREAM, RESOURCE_LIVE_VIDEO, id, { replace: true });
  } else {
    navigate(`/live-video/dashboard/${id}`);
  }
}

const sagas = [takeEvery('@updatedItem/live_video', updatedItem)];

export default sagas;
