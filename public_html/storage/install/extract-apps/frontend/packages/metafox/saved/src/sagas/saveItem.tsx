/**
 * @type: saga
 * name: save.saveItem
 */

import { deleteItem } from '@metafox/core/sagas/deleteItem';
import {
  AppResourceAction,
  fulfillEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  getResourceAction,
  getResourceConfig,
  handleActionConfirm,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  PAGINATION_REFRESH,
  patchEntity,
  ENTITY_DELETE,
  PAGINATION_UN_LIST,
  deleteEntity,
  PAGINATION_UNSHIFT
} from '@metafox/framework';
import { difference, isFunction, xor } from 'lodash';
import { put, takeEvery, call } from 'redux-saga/effects';
import {
  APP_SAVED,
  PAGINGID_SAVED_LIST_ADD,
  RESOURCE_SAVED_LIST,
  RESOURCE_SAVED_LIST_MEMBER
} from '../constant';
import { getPagingIdListSaved } from '../utils';

const normalizeSaveItem = data => {
  data.module_name = APP_SAVED;
  data.resource_name = APP_SAVED;
};

function* reloadRemoveListItemInPageCollection(identity: any) {
  const { getPageParams } = yield* getGlobalContext();

  const pageParams: any = getPageParams();

  yield put({
    type: PAGINATION_UN_LIST,
    payload: {
      identity,
      pagingId: getPagingIdListSaved(pageParams)
    }
  });
}

function* reloadSavedList() {
  const { compactData, compactUrl, getPageParams } = yield* getGlobalContext();

  const config = yield* getResourceAction(
    APP_SAVED,
    RESOURCE_SAVED_LIST,
    'viewAll'
  );

  const params = getPageParams();

  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: compactUrl(config.apiUrl, params),
      apiParams: compactData(config?.apiParams, params),
      pagingId: 'saveditems-collection?'
    }
  });
}

function* reloadSavedItems() {
  const { compactData, compactUrl, getPageParams } = yield* getGlobalContext();

  const config = yield* getResourceAction(APP_SAVED, APP_SAVED, 'viewAll');

  const params = getPageParams();

  yield put({
    type: PAGINATION_REFRESH,
    payload: {
      apiUrl: compactUrl(config.apiUrl, params),
      apiParams: compactData(config?.apiParams, params),
      pagingId: 'paging_saved_items'
    }
  });
}

function* updateNumberItemFromIdentity(
  identity: string,
  type: 'add' | 'remove'
) {
  const item = yield* getItem(identity);

  if (!item && !identity) return;

  try {
    if (type === 'add') {
      yield* patchEntity(identity, {
        statistic: {
          ...item.statistic,
          total_saved_item: item.statistic.total_saved_item + 1
        }
      });
    }

    if (type === 'remove') {
      let total_saved_item = 0;

      if (item.statistic.total_saved_item > 0)
        total_saved_item = item.statistic.total_saved_item - 1;

      yield* patchEntity(identity, {
        statistic: {
          ...item.statistic,
          total_saved_item
        }
      });
    }
    // eslint-disable-next-line no-empty
  } catch (error) {}
}

export function* saveItem(action: {
  type: string;
  payload: { identity: string };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const {
    apiClient,
    compactUrl,
    getSetting,
    normalization,
    dialogBackend,
    getAcl
  } = yield* getGlobalContext();
  const hasSavedCollection = getAcl('saved.saved_list.update');
  const enable_saved_collection = getSetting('saved.enable_saved_in_detail');

  if (!item) return;

  const { is_saved: value } = item;

  const config = yield* getResourceAction(APP_SAVED, APP_SAVED, 'saveItem');

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    yield* patchEntity(identity, { is_saved: !item.is_saved });
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: compactUrl(config.apiUrl, item),
      data: {
        item_id: 'feed' === item.resource_name ? item.item_id : item.id,
        item_type: item.item_type || item.resource_name,
        in_feed: 'feed' === item.resource_name ? 1 : 0,
        link: item.link
      }
    });

    const data = response?.data?.data;
    normalizeSaveItem(data);
    const result = normalization.normalize(data);

    yield* fulfillEntity(result.data);

    yield* handleActionFeedback(response);

    if (data && hasSavedCollection && enable_saved_collection) {
      yield dialogBackend.present({
        component: 'saved.dialog.saveToCollection',
        props: {
          item: data
        }
      });

      return;
    }
  } catch (error) {
    yield* patchEntity(identity, { is_saved: value });
    yield* handleActionError(error);
  }
}

export function* undoSaveItem(action: {
  type: string;
  payload: { identity: string };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactData, getSetting } = yield* getGlobalContext();
  const enable_unsaved_confirmation = getSetting(
    'saved.enable_unsaved_confirmation'
  );

  if (!item) return;

  const { is_saved: value } = item;

  const params = {
    item_id: 'feed' === item.resource_name ? item.item_id : item.id,
    item_type: item.item_type || item.resource_name,
    in_feed: 'feed' === item.resource_name ? 1 : 0,
    like_type: item.like_type_id
  };
  const config = yield* getResourceAction(APP_SAVED, APP_SAVED, 'undoSaveItem');

  if (!config?.apiUrl) return;

  if (enable_unsaved_confirmation) {
    const ok = yield* handleActionConfirm(config);

    if (!ok) return;
  }

  try {
    yield* patchEntity(identity, { is_saved: 0 });
    const response = yield apiClient.request({
      method: config.apiMethod,
      url: config.apiUrl,
      params: compactData(config.apiParams, params)
    });
    yield* handleActionFeedback(response);
  } catch (error) {
    yield* patchEntity(identity, { is_saved: value });
    yield* handleActionError(error);
  }
}

export function* addSavedItemToCollection(action: {
  type: string;
  payload: {
    identity: string;
    ids: string[];
    initCollections: string[];
  };
  meta: { onSuccess: () => void };
}) {
  const { identity, ids, initCollections } = action.payload;
  const { onSuccess } = action.meta;
  const item = yield* getItem(identity);
  const { apiClient, getPageParams } = yield* getGlobalContext();
  const params = getPageParams() as any;

  if (!item) return;

  const config: AppResourceAction = yield* getItemActionConfig(
    item,
    'moveItem'
  );

  if (!config?.apiUrl) return;

  try {
    const res = yield apiClient.request({
      url: config.apiUrl,
      method: config.apiMethod,
      data: { item_id: item.id, collection_ids: ids }
    });

    yield* handleActionFeedback(res);

    if (!res?.data.data) return;

    const {
      belong_to_collection,
      default_collection_id,
      default_collection_name,
      collection_ids,
      statistic
    } = res?.data.data;

    yield* patchEntity(identity, {
      belong_to_collection,
      default_collection_id,
      default_collection_name,
      collection_ids,
      statistic
    });

    const removeIds = difference(initCollections, ids);

    if (removeIds.length) {
      yield* removeIds.map((id: string) => {
        const identity = `saved.entities.saved_list.${id}`;

        return call(updateNumberItemFromIdentity, identity, 'remove');
      });
    } else {
      const addIds = xor(initCollections, ids);

      if (addIds.length) {
        yield* addIds.map((id: string) => {
          const identity = `saved.entities.saved_list.${id}`;

          return call(updateNumberItemFromIdentity, identity, 'add');
        });
      }
    }

    if (
      initCollections.some(x => x.toString() === params?.collection_id) &&
      removeIds.includes(+params?.collection_id)
    )
      yield* reloadRemoveListItemInPageCollection(identity);

    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeSavedItemToCollection(action: {
  type: string;
  payload: { identity: string; collection_id: string };
  meta: { onSuccess: () => void };
}) {
  const { identity, collection_id } = action.payload;
  const { onSuccess } = action.meta;
  const item = yield* getItem(identity);
  const { apiClient } = yield* getGlobalContext();

  const identityCollection = `saved.entities.saved_list.${collection_id}`;

  if (!item || !collection_id) return;

  const config: AppResourceAction = yield* getItemActionConfig(
    item,
    'moveItem'
  );

  if (!config?.apiUrl) return;

  try {
    const res = yield apiClient.request({
      url: config.apiUrl,
      method: config.apiMethod,
      data: { item_id: item.id, collection_ids: [collection_id], is_removed: 1 }
    });

    yield* handleActionFeedback(res);

    if (!res?.data.data) return;

    const {
      belong_to_collection,
      default_collection_id,
      default_collection_name,
      collection_ids,
      statistic
    } = res?.data.data;

    yield* patchEntity(identity, {
      belong_to_collection,
      default_collection_id,
      default_collection_name,
      collection_ids,
      statistic
    });

    yield* updateNumberItemFromIdentity(identityCollection, 'remove');

    isFunction(onSuccess) && onSuccess();
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* addNewCollection(action) {
  const { meta } = action;

  try {
    const { dialogBackend } = yield* getGlobalContext();

    const dataSource = yield* getResourceConfig(
      APP_SAVED,
      'saved_list',
      'addItem'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm'
      }
    });

    if (data) {
      if (meta?.onSuccess) {
        meta.onSuccess(data);
      }
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* editItemCollection({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();
    const savedList = yield* getItem(identity);

    const dataSource = yield* getResourceConfig(
      APP_SAVED,
      'saved_list',
      'editItem'
    );

    const data = yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource,
        maxWidth: 'sm',
        initialValues: {
          name: savedList?.name
        },
        pageParams: { id: savedList?.id }
      }
    });

    if (data) {
      yield* reloadSavedList();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* deleteItemSaved({ payload: { identity } }: ItemLocalAction) {
  const saved = yield* getItem(identity);

  if (!saved) return;

  try {
    const result = yield* deleteItem({ payload: { identity } } as any);

    if (!result) return;

    if (saved?.collection_ids?.length) {
      yield* saved.collection_ids.map((id: string) => {
        const identity = `saved.entities.saved_list.${id}`;

        return call(updateNumberItemFromIdentity, identity, 'remove');
      });
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* deleteItemCollection({ payload: { identity } }: ItemLocalAction) {
  const collection = yield* getItem(identity);

  if (!collection) return;

  try {
    const result = yield* deleteItem({ payload: { identity } } as any);

    if (!result) return;

    if (collection?.statistic?.total_saved_item > 0) {
      yield* reloadSavedItems();
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* addFriend({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend } = yield* getGlobalContext();
    const friendList = yield* getItem(identity);

    const dataSource = yield* getResourceConfig(
      APP_SAVED,
      RESOURCE_SAVED_LIST,
      'addFriend'
    );

    yield dialogBackend.present({
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
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* openDialogFriend({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend, compactData, compactUrl } =
      yield* getGlobalContext();
    const item = yield* getItem(identity);

    const dataSource = yield* getItemActionConfig(item, 'viewFriend');

    yield dialogBackend.present({
      component: 'saved.dialog.friendList',
      props: {
        apiUrl: compactUrl(dataSource.apiUrl, item),
        apiParams: compactData(dataSource.apiParams, { saved_id: item?.id }),
        dialogTitle: 'members_list'
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* removeMemberInCollection({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const config = yield* getResourceConfig(
    APP_SAVED,
    RESOURCE_SAVED_LIST_MEMBER,
    'removeMember'
  );

  const configListItem = yield* getResourceConfig(
    APP_SAVED,
    RESOURCE_SAVED_LIST,
    'viewFriend'
  );

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const { compactData, compactUrl, apiClient } = yield* getGlobalContext();

    const response = yield apiClient.request({
      url: compactUrl(config.apiUrl, { id: item.collection_id }),
      method: config.apiMethod,
      data: compactData(config?.apiParams, { user_id: item.id })
    });

    yield put({
      type: ENTITY_DELETE,
      payload: {
        identity,
        pagingId: `${compactUrl(configListItem.apiUrl, {
          id: item.collection_id
        })}?`
      }
    });

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* removeCollectionItem(action: {
  type: string;
  payload: { identity: string };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, getPageParams, compactUrl } = yield* getGlobalContext();

  const { collection_id } = getPageParams() as any;

  const identityCollection = `saved.entities.saved_list.${collection_id}`;

  if (!item || !collection_id) return;

  const config: AppResourceAction = yield* getItemActionConfig(
    item,
    'removeCollectionItem'
  );

  if (!config?.apiUrl) return;

  try {
    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, {
        saved_id: item.id,
        list_id: collection_id
      }),
      method: config.apiMethod
    });

    yield* handleActionFeedback(res);

    if (!res?.data.data) return;

    yield* reloadRemoveListItemInPageCollection(identity);

    yield* updateNumberItemFromIdentity(identityCollection, 'remove');
  } catch (error) {
    yield* handleActionError(error);
  }
}

export function* leaveCollection(action: {
  type: string;
  payload: { identity: string };
}) {
  const { identity } = action.payload;
  const item = yield* getItem(identity);
  const { apiClient, compactUrl, getPageParams, navigate } =
    yield* getGlobalContext();

  const { collection_id } = getPageParams() as any;

  if (!item) return;

  const config: AppResourceAction = yield* getItemActionConfig(
    item,
    'leaveCollection'
  );

  if (!config?.apiUrl) return;

  const ok = yield* handleActionConfirm(config);

  if (!ok) return;

  try {
    const res = yield apiClient.request({
      url: compactUrl(config.apiUrl, item),
      method: config.apiMethod
    });
    yield* deleteEntity(identity);

    yield* handleActionFeedback(res);

    if (collection_id) {
      navigate('/saved', { replace: true });
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* addNewCollectionName(action: {
  type: string;
  payload: { name: string };
  meta: any;
}) {
  const { name } = action.payload;

  if (!name) return;

  try {
    const { apiClient, normalization } = yield* getGlobalContext();

    const config = yield* getResourceConfig(
      APP_SAVED,
      'saved_list',
      'createCollection'
    );

    const response = yield apiClient.request({
      url: config.apiUrl,
      method: config.apiMethod,
      data: { ...config.apiParams, name }
    });

    yield* handleActionFeedback(response);

    const data = response?.data?.data;

    if (data) {
      yield* reloadSavedList();

      const result = normalization.normalize(data);

      yield* fulfillEntity(result.data);

      const identity = `saved.entities.saved_list.${data?.id}`;

      yield put({
        type: PAGINATION_UNSHIFT,
        payload: {
          data: [identity],
          pagingId: [PAGINGID_SAVED_LIST_ADD]
        }
      });
    }
  } catch (err) {
    yield* handleActionError(err);
  }
}

function* viewCollectionItemSaved({ payload: { identity } }: ItemLocalAction) {
  try {
    const { dialogBackend, compactData } = yield* getGlobalContext();
    const item = yield* getItem(identity);

    const dataSource = yield* getResourceAction(
      APP_SAVED,
      RESOURCE_SAVED_LIST,
      'getCollectionByItem'
    );

    yield dialogBackend.present({
      component: 'saved.dialog.viewCollectionItemSaved',
      props: {
        dataSource: {
          apiUrl: dataSource.apiUrl,
          apiParams: compactData(dataSource.apiParams, { id: item?.id })
        },
        dialogTitle: 'collections'
      }
    });
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [
  takeEvery('saveItem', saveItem),
  takeEvery('undoSaveItem', undoSaveItem),
  takeEvery('addSavedItemToCollection', addSavedItemToCollection),
  takeEvery('removeSavedItemToCollection', removeSavedItemToCollection),
  takeEvery('saved/addNewCollection', addNewCollection),
  takeEvery('saved_list/editList', editItemCollection),
  takeEvery('saved/deleteItem', deleteItemSaved),
  takeEvery('saved_list/deleteItem', deleteItemCollection),
  takeEvery('saved_list/addFriend', addFriend),
  takeEvery('saved_list/viewFriend', openDialogFriend),
  takeEvery('saved/removeMemberInCollection', removeMemberInCollection),
  takeEvery('saved/removeCollectionItem', removeCollectionItem),
  takeEvery('saved_list/leaveCollection', leaveCollection),
  takeEvery('saved/addNewCollectionName', addNewCollectionName),
  takeEvery('saved/viewCollectionItemSaved', viewCollectionItemSaved)
];

export default sagas;
