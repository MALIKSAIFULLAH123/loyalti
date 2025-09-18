/**
 * @type: saga
 * name: comment.showHistoryDialog
 */

import {
  getGlobalContext,
  handleActionError,
  getResourceAction,
  getItem
} from '@metafox/framework';
import { takeLatest } from 'redux-saga/effects';

function* showHistoryDialog(action: {
  type: string;
  payload: {
    identity: string;
  };
  meta: { onSuccess: (data: any) => {} };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);

  if (!item) return;

  const { dialogBackend } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    'comment',
    'comment',
    'viewCommentHistories'
  );

  if (!config?.apiUrl) return;

  try {
    yield dialogBackend.present({
      component: 'comment.dialog.historyList',
      props: {
        dataSource: config,
        pageParams: {
          id: item?.id
        }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeLatest('comment/showHistoryDialog', showHistoryDialog)];

export default sagas;
