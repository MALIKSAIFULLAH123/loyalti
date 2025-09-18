/**
 * @type: saga
 * name: forum_post.saga.quotePost
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';

function* quotePost({ payload }: ItemLocalAction) {
  const { identity } = payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { compactUrl, dialogBackend } = yield* getGlobalContext();

  const config = yield* getItemActionConfig(item, 'quoteItem');

  if (!config?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'forum.dialog.QuotePostDialog',
      props: {
        dataSource: { apiUrl: compactUrl(config.apiUrl, item) },
        parentPost: item
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('forum/quoteItem', quotePost)];

export default sagas;
