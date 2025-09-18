/**
 * @type: saga
 * name: saga.group.myPendingManage
 */

import {
  getGlobalContext,
  handleActionError,
  ItemLocalAction
} from '@metafox/framework';
import { takeEvery } from 'redux-saga/effects';
import { compactUrl } from '@metafox/utils';

function* openMyManagePage(
  action: ItemLocalAction<{ identity: string; item: any }>
) {
  const {
    payload: { identity, item }
  } = action;
  const { navigate } = yield* getGlobalContext();

  try {
    const id = identity.split('.')[3];

    if (
      item?.is_admin ||
      (item?.is_moderator && item?.extra?.can_manage_pending_posts)
    ) {
      navigate(
        compactUrl('/group/manage/:id/pending_posts', {
          id
        })
      );
    } else {
      navigate(
        compactUrl('/group/:id/review_my_content/pending', {
          id
        })
      );
    }
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [takeEvery('group/openMyManagePage', openMyManagePage)];

export default sagas;
