/**
 * @type: saga
 * name: comment.saga.editComment
 */

import {
  getGlobalContext,
  getItem,
  handleActionError,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { all, call, put, select, takeEvery } from 'redux-saga/effects';
import { getCommentExceptIdentitySelector } from '../selectors';
import { CommentItemShape } from '../types';

export function* getCommentExceptIdentity(
  commentIdentity: string
): Generator<unknown, string[], unknown> {
  return (yield select(
    getCommentExceptIdentitySelector,
    commentIdentity
  )) as any;
}

function* getEditText(id: number) {
  const { apiClient } = yield* getGlobalContext();

  const response = yield apiClient.request({
    method: 'get',
    url: `/comment/${id}`
  });

  return response.data?.data?.text_raw;
}

function* editComment(action: ItemLocalAction) {
  const {
    payload: { identity }
  } = action;
  const item: CommentItemShape = yield* getItem(identity);

  if (!item) return;

  yield* patchEntity(identity, { isLoading: true });

  const arrayComment = yield* getCommentExceptIdentity(identity);

  yield all(
    arrayComment.map(identity =>
      call(cancelEdit, { payload: { identity } } as ItemLocalAction)
    )
  );
  try {
    const editText = yield* getEditText(item.id as number);

    yield* patchEntity(identity, {
      isEditing: true,
      isLoading: false,
      editText
    });
  } catch (error) {
    yield* handleActionError(error);
    yield* patchEntity(identity, {
      isEditing: false,
      isLoading: false
    });
  }
}

function* cancelEdit({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);

  if (!item) return null;

  yield* patchEntity(identity, {
    editText: undefined,
    isEditing: false,
    isLoading: false,
    hideExtraData: false
  });

  yield;
}

export function* updateComment(action) {
  const { text, identity } = action.payload;
  const { meta } = action;
  const item = yield* getItem(identity);

  const { text: oldText } = item;
  const mentionReg =
    /\[(?:user|page|group)=(\d+)\]([^[]+)\[\/(?:user|page|group)\]/gm;

  yield* patchEntity(identity, {
    text: text?.replace(mentionReg, '$2'),
    isEditing: false,
    isLoading: true,
    hideExtraData: true
  });

  yield put({
    type: 'comment/composer/CALL',
    payload: { ...action.payload, oldText },
    meta
  });
}

const sagas = [
  takeEvery('editComment', editComment),
  takeEvery('comment/composer/UPDATE', updateComment),
  takeEvery('comment/editComment/CANCEL', cancelEdit),
  takeEvery('comment/editComment/SUCCESS', cancelEdit)
];

export default sagas;
