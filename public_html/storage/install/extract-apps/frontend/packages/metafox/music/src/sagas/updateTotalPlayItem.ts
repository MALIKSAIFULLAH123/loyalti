/**
 * @type: saga
 * name: music.saga.updateTotalPlay
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* updateTotalPlay({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  const { statistic } = item;

  if (!item) return;

  const { compactUrl, apiClient } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'updateTotalPlayItem');

  if (!config?.apiUrl) return;

  try {
    yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod
    });
    yield* patchEntity(identity, {
      statistic: {
        ...statistic,
        music_song_total_play: statistic?.music_song_total_play + 1
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('music/updateTotalPlayItem', updateTotalPlay)];

export default sagas;
