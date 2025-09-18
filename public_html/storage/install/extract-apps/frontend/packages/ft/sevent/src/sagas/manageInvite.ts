/**
 * @type: saga
 * name: sevent/addTicketItem
 */

import {
  getGlobalContext,
  getItem,
  ItemLocalAction
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* addTicketItem({ payload: { identity } }: ItemLocalAction) {
  const { navigate } = yield* getGlobalContext();
  const item = yield* getItem(identity);

  navigate(`/sevent/ticket/add?sevent_id=${item.id}`);
}

const sagas = [
  takeLatest('sevent/addTicketItem', addTicketItem)
];

export default sagas;
