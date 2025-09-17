/**
 * @type: saga
 * name: core.viewEditHistory
 */
import {
  getGlobalContext,
  getItem,
  ItemLocalAction,
  getResourceAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { APP_FEED } from '@metafox/feed';

function* viewEditHistory(action: ItemLocalAction) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  const { dialogBackend } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    APP_FEED,
    'feed_history',
    'viewHistories'
  );

  try {
    yield dialogBackend.present({
      component: 'feed.status.viewEditHistoryDialog',
      props: {
        dataSource: config,
        pageParams: {
          id: item?.id
        }
      }
    });
  } catch (err) {
    // console.log(err);
  }
}

const sagas = [takeEvery('feed_history/viewHistories', viewEditHistory)];

export default sagas;
