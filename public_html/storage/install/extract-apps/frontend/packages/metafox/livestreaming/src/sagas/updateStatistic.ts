/**
 * @type: saga
 * name: livestreaming.saga.updateStatistic
 */

import {
  getItem,
  ItemLocalAction,
  getSession,
  patchEntity
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';
import { pickBy } from 'lodash';
import { ReactionLiveType } from '../types';

function* updateStatistic(
  action: ItemLocalAction & {
    payload: ReactionLiveType;
  }
) {
  const { identity, statistic, most_reactions_information } = action.payload;
  const item = yield* getItem(identity);
  const { loggedIn } = yield* getSession();

  if (!item || !item?.is_streaming || !loggedIn) return;

  try {
    const data = {
      most_reactions_information,
      statistic: { ...item?.statistic, ...statistic }
    };
    const cleanData = pickBy(data, v => v !== undefined);
    yield* patchEntity(identity, cleanData);
  } catch (error) {}
}

const sagas = [takeLatest('livestreaming/updateStatistic', updateStatistic)];

export default sagas;
