/**
 * @type: saga
 * name: friend.editList
 */
import {
  getGlobalContext,
  getItem,
  getResourceConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_REFRESH,
  makeDirtyPaging
} from '@metafox/framework';
import { UserItemShape } from '@metafox/user';
import { isArray } from 'lodash';
import { put, takeEvery } from 'redux-saga/effects';

function* reloadFriendList() {
  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: '/friend/list',
      apiParams: {},
      pagingId: '/friend/list?'
    }
  });
}

function* addFriend({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();
    const friendList = yield* getItem(identity);

    const dataSource = yield* getResourceConfig(
      'friend',
      'friend_list',
      'addItems'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'xs',
        initialValues: {
          name: friendList?.name
        },
        pageParams: { id: friendList?.id }
      }
    });

    if (data) {
      yield* reloadFriendList();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* editList({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();
    const friendList = yield* getItem(identity);

    const dataSource = yield* getResourceConfig(
      'friend',
      'friend_list',
      'editList'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm',
        initialValues: {
          name: friendList?.name
        },
        pageParams: { id: friendList?.id }
      }
    });

    if (data) {
      yield* reloadFriendList();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* editItem({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();
    const friendList = yield* getItem(identity);

    const dataSource = yield* getResourceConfig(
      'friend',
      'friend_list',
      'editItems'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'xs',

        pageParams: { id: friendList?.id }
      }
    });

    if (data) {
      yield* reloadFriendList();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* assignFriendList({ payload: { identity } }: ItemLocalAction) {
  try {
    const item = yield* getItem<UserItemShape>(identity);

    if (!item) return;

    const { dialogBackend, apiClient } = yield* getGlobalContext();

    const list_id = yield dialogBackend.present({
      component: 'friend.dialog.AssignFriendList',
      props: { id: item.id }
    });

    if (!isArray(list_id)) return;

    const response = yield apiClient.request({
      method: 'POST',
      url: `/friend/list/assign/${item.id}`,
      data: { list_id }
    });

    yield* handleActionFeedback(response);
    yield* makeDirtyPaging('friend/?list_id');

    yield* reloadFriendList();
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* addFriendList(action) {
  const { meta } = action;

  try {
    const { dialogBackend, navigate } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig(
      'friend',
      'friend_list',
      'addList'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm'
      }
    });

    if (data) {
      if (data?.id) {
        navigate(`/friend/list/${data?.id}`);
      }

      yield* reloadFriendList();

      if (meta?.onSuccess) {
        meta.onSuccess(data);
      }
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* addFriendListWhenCustomPrivacy(action) {
  const { meta } = action;

  try {
    const { dialogBackend } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig(
      'core',
      'privacy',
      'getCreatePrivacyOptionForm'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm'
      }
    });

    if (data) {
      yield* reloadFriendList();

      if (meta?.onSuccess) {
        meta.onSuccess(data);
      }
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* deleteFriendListDone({ payload }) {
  const { navigate, getPageParams } = yield* getGlobalContext();
  const { list_id } = getPageParams();

  if (list_id?.toString() === payload?.id.toString()) {
    navigate('/friend', { replace: true });
  }
}

const sagas = [
  takeEvery('friend_list/editList', editList),
  takeEvery('friend_list/editItem', editItem),
  takeEvery('friend_list/addFriend', addFriend),
  takeEvery('friend/addNewList', addFriendList),
  takeEvery(
    'friend/addNewListWhenCustomPrivacy',
    addFriendListWhenCustomPrivacy
  ),
  takeEvery('friend/assignFriendList', assignFriendList),
  takeEvery('friend/friend_list/deleteItem/SUCCESS', reloadFriendList),
  takeEvery('friend/friend_list/deleteItem/DONE', deleteFriendListDone)
];

export default sagas;
