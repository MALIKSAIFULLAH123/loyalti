/**
 * @type: saga
 * name: reaction.showPeopleWhoReactThisItem
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  patchEntity
} from '@metafox/framework';
import { all, call, takeEvery, takeLatest } from 'redux-saga/effects';

export function* showPeopleWhoReactThisItem(action: {
  type: string;
  payload: { identity: string; reactIdentity?: string };
}) {
  const { identity, reactIdentity } = action.payload;
  const item = yield* getItem(identity);
  const reactItem = yield* getItem(reactIdentity);

  if (!item) return;

  const { like_type_id, item_type, resource_name, like_item_id, item_id, id } =
    item;

  const { dialogBackend } = yield* getGlobalContext();

  dialogBackend.present({
    component: 'reaction.dialog.PeopleWhoReactionThis',
    props: {
      identity,
      item_type: like_type_id || item_type || resource_name,
      item_id: like_item_id || item_id || id,
      item,
      reactId: reactItem?.id ?? undefined
    }
  });
}

function* getReactedListUser(action: {
  type: string;
  payload: {
    item_type: string;
    react_id: string;
    item_id: string;
    limit: number;
    like_item_id: string;
    like_type_id: string;
    resource_name: string;
    id: number;
  };
  meta: { onSuccess: (data: any) => {} };
}) {
  const { apiClient } = yield* getGlobalContext();

  const {
    item_type,
    react_id,
    item_id,
    limit,
    resource_name,
    like_item_id,
    like_type_id,
    id
  } = action.payload;

  const { onSuccess } = action.meta;

  const sendData = {
    item_id: like_item_id || item_id || id,
    item_type: like_type_id || item_type || resource_name,
    react_id,
    limit
  };

  try {
    const response = yield apiClient.request({
      url: '/preaction/get-reacted-lists',
      params: sendData
    });

    const data = response.data.data;
    const meta = response.data?.meta;

    typeof onSuccess === 'function' && onSuccess({ data, meta });
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* updateReaction(item) {
  try {
    const entitiesReaction = `preaction.entities.preaction.${item.id}`;

    const itemPreaction = getItem(entitiesReaction);

    if (itemPreaction) {
      const entityPreaction = { ...itemPreaction, icon: item.icon };

      yield* patchEntity(entitiesReaction, entityPreaction);
    }
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

export function* updateResourceReaction(action: {
  type: string;
  payload: { tabResourceReaction?: any };
}) {
  const { tabResourceReaction } = action.payload;

  if (!tabResourceReaction.length) return;

  yield all(
    tabResourceReaction
      .filter(x => x.icon)
      .map(item => call(updateReaction, item))
  );
}

const sagas = [
  takeLatest('showPeopleWhoReactThisItem', showPeopleWhoReactThisItem),
  takeLatest('react/getReactedListUser', getReactedListUser),
  takeEvery('updateResourceReaction', updateResourceReaction)
];

export default sagas;
