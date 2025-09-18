/**
 * @type: saga
 * name: marketplace/manageInvite
 */

import {
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  getResourceAction
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';
import { APP_MARKETPLACE } from '@metafox/marketplace/constants';

function* viewInvitedPeople({ payload: { identity } }: ItemLocalAction) {
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();
  const config = yield* getResourceAction(
    APP_MARKETPLACE,
    APP_MARKETPLACE,
    'viewInvitedPeople'
  );
  const apiParams = compactData(config.apiParams, item, config.apiRules);

  yield dialogBackend.present({
    component: 'marketplace.dialog.viewInvitedDialog',
    props: {
      item,
      dataSource: {
        apiUrl: config.apiUrl,
        apiParams
      }
    }
  });
}

function* invitePeopleToCome(
  action: ItemLocalAction & {
    payload: { identity: string };
  }
) {
  const {
    payload: { identity }
  } = action;
  const item = yield* getItem(identity);

  if (!identity && !item) return;

  const id = identity.split('.')[3];
  const { owner, user } = item;
  const owner_id = owner.split('.')[3];
  const user_id = user.split('.')[3];

  try {
    const { dialogBackend, apiClient, i18n } = yield* getGlobalContext();

    const dataSource = yield* getItemActionConfig(
      { module_name: APP_MARKETPLACE, resource_name: APP_MARKETPLACE },
      'suggestFriends'
    );
    const selectedItems = yield dialogBackend.present({
      component: 'friend.dialog.MultipleFriendPicker',
      props: {
        apiUrl: dataSource.apiUrl,
        initialParams: compactData(dataSource.apiParams, {
          id,
          owner_id,
          user_id
        }),
        dialogTitle: i18n.formatMessage({ id: 'invite_people_to_come' })
      }
    });

    if (!selectedItems?.length) return;

    const userIds = selectedItems.map(item => item.id);

    const config = yield* getItemActionConfig(
      { module_name: APP_MARKETPLACE, resource_name: APP_MARKETPLACE },
      'invitePeopleToCome'
    );

    if (!config) return;

    const { apiMethod, apiUrl, apiParams } = config;

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        id,
        ids: userIds
      })
    });

    yield* handleActionFeedback(response);
  } catch (err) {
    yield* handleActionError(err);
  }
}

const sagas = [
  takeLatest('marketplace/viewInvitedPeople', viewInvitedPeople),
  takeLatest('marketplace/invitePeopleToCome', invitePeopleToCome)
];

export default sagas;
