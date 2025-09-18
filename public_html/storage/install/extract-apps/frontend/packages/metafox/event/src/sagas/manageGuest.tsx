/**
 * @type: saga
 * name: event/manageGuest
 */

import {
  deleteEntity,
  getGlobalContext,
  getItem,
  getItemActionConfig,
  handleActionError,
  handleActionFeedback,
  ItemLocalAction,
  patchEntity
} from '@metafox/framework';
import { compactData } from '@metafox/utils';
import { takeLatest } from 'redux-saga/effects';
import { APP_EVENT } from '../constants';

function* viewAllGuest({
  payload: { identity, defaultTab }
}: ItemLocalAction & { payload: { defaultTab: string } }) {
  const item = yield* getItem(identity);
  const { dialogBackend } = yield* getGlobalContext();

  yield dialogBackend.present({
    component: 'event.dialog.viewGuestDialog',
    props: {
      item,
      defaultTab
    }
  });
}

function* removeGuestMember(
  action: ItemLocalAction & {
    payload: { identity: string; onSuccess: any };
  }
) {
  const {
    payload: { identity, onSuccess }
  } = action;

  const { apiClient } = yield* getGlobalContext();

  try {
    const item = yield* getItem(identity);

    const { event_id, user } = item;
    const _identity_event = `event.entities.event.${event_id}`;
    const itemEvent = yield* getItem(_identity_event);

    const dataSource = yield* getItemActionConfig(
      { module_name: APP_EVENT, resource_name: 'event_member' },
      'removeMember'
    );

    const { apiUrl, apiMethod, apiParams } = dataSource;

    const userId = user.split('.')[3];

    const response = yield apiClient.request({
      method: apiMethod,
      url: apiUrl,
      params: compactData(apiParams, {
        event_id,
        user_id: userId
      })
    });

    yield* deleteEntity(identity);

    if (onSuccess) onSuccess();

    const data = response.data.data;

    if (data && itemEvent) {
      const statistic = {
        ...itemEvent.statistic,
        total_member: data.total_member,
        total_interested: data.total_interested
      };

      yield* patchEntity(_identity_event, {
        statistic
      });
    }

    yield* handleActionFeedback(response);
  } catch (error) {
    yield* handleActionError(error);
  }
}

function* massEmail(
  action: ItemLocalAction & {
    payload: { identity: string };
  }
) {
  const {
    payload: { identity }
  } = action;

  const { dialogBackend, compactUrl } = yield* getGlobalContext();
  const item = yield* getItem(identity);
  const config = yield* getItemActionConfig(item, 'massEmailEvent');

  try {
    yield dialogBackend.present({
      component: 'core.dialog.RemoteForm',
      props: {
        dataSource: {
          apiUrl: compactUrl(config.apiUrl, item)
        }
      }
    });
  } catch (error) {
    yield* handleActionError(error);
  }
}

const sagas = [
  takeLatest('event/viewAllGuest', viewAllGuest),
  takeLatest('event_member/removeMember', removeGuestMember),
  takeLatest('event/massEmailEvent', massEmail)
];

export default sagas;
