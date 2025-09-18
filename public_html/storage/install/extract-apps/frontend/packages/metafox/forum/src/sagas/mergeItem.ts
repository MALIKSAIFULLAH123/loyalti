/**
 * @type: saga
 * name: forum_thread.saga.mergeItem
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction,
  fulfillEntity,
  deleteEntity,
  viewItem
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { ForumThreadShape } from '@metafox/forum/types';

function* mergeItem({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'mergeItem');

  if (!config?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'forum.dialog.FormDialog',
      props: {
        formUrl: compactUrl(config.apiUrl, item)
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

type MergeAction = {
  type: string;
  payload: {
    new_item: ForumThreadShape & { id: string };
    old_id: string;
  };
};

function* successMerge({ payload }: MergeAction) {
  const { new_item, old_id } = payload;
  const { normalization } = yield* getGlobalContext();

  if (!new_item || !old_id) return;

  const result = normalization.normalize(new_item);
  yield* fulfillEntity(result.data);
  yield* deleteEntity(`forum.entities.forum_thread.${old_id}`);
  yield* viewItem('forum', 'forum_thread', new_item?.id);
}

const sagas = [
  takeEvery('forum/mergeItem', mergeItem),
  takeEvery('forum/mergedItem', successMerge)
];

export default sagas;
