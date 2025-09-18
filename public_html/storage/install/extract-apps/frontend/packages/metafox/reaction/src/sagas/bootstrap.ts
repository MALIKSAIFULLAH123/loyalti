/**
 * @type: saga
 * name: reaction.bootstrap
 */
import { fulfillEntity, getGlobalContext } from '@metafox/framework';
import { ACTION_LOAD_SETTING } from '@metafox/framework/state/constants';
import { get, isArray } from 'lodash';
import { put, takeLatest } from 'redux-saga/effects';

export function* init(action) {
  const { normalization } = yield* getGlobalContext();

  const data = get(action.payload, 'settings.preaction.reaction_list');

  if (!isArray(data)) return;

  const reactions = data.map(item => ({
    ...item,
    icon: undefined,
    src: item.icon
  }));
  let unreactedItem = data.find(x => x.is_default);
  const result = normalization.normalize(reactions);

  if (!unreactedItem && reactions.length) {
    unreactedItem = reactions[0];
  }

  yield* fulfillEntity(result.data);

  yield put({
    type: 'reaction/bootstrap',
    payload: {
      ids: result.ids,
      reactions,
      unreactedItem: {
        ...unreactedItem,
        src: undefined,
        color: undefined,
        icon: unreactedItem?.icon_font || unreactedItem?.icon
      }
    }
  });
}

const sagas = [takeLatest(ACTION_LOAD_SETTING, init)];

export default sagas;
