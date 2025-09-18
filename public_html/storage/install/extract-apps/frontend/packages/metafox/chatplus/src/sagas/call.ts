/**
 * @type: saga
 * name: saga.chatplus.call
 */
import { getGlobalContext, ItemLocalAction } from '@metafox/framework';
import { put, takeEvery, takeLatest } from 'redux-saga/effects';
import handleActionErrorChat from './handleActionErrorChat';

function* startVoiceCall(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  if (!identity) return null;

  try {
    yield chatplus.startNewCall(identity, true);
  } catch (error) {
    // err
  }
}

function* startVideoChat(action: ItemLocalAction) {
  const { identity } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  if (!identity) return null;

  try {
    yield chatplus.startNewCall(identity, false);
  } catch (error) {
    // err
  }
}

function* getCallInfo(
  action: ItemLocalAction<
    { callId: string; showPopupCall?: boolean },
    { onSuccess: () => void }
  >
) {
  const { callId, showPopupCall = true } = action.payload;

  if (!callId) return;

  const { chatplus, dialogBackend } = yield* getGlobalContext();

  try {
    const result = yield chatplus.waitDdpMethod({
      name: 'metafox/call/info',
      params: [callId]
    });

    if (!result) return null;

    yield put({ type: 'chatplus/callInfo', payload: result });

    if (showPopupCall) {
      yield dialogBackend.present({
        component: 'dialog.IncommingCallPopup',
        props: { callId }
      });
    }

    action?.meta?.onSuccess && action.meta.onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* getCallRoom(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;

  if (!callId) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.getCallRoom(callId);

    if (!result) return null;

    yield chatplus.getCallNotify(callId);
    yield put({ type: 'chatplus/callInfo', payload: result });
  } catch (error) {
    // yield* handleActionErrorChat(error);
  }
}

function* rejectCallFromPopup(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;

  if (!callId) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.rejectCallFromPopup(callId);

    if (!result) return null;
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* acceptCallFromPopup(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;

  if (!callId) return;

  const { chatplus } = yield* getGlobalContext();

  try {
    const result = yield chatplus.acceptCallFromPopup(callId);

    if (!result) return null;
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* cancelCall(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.waitDdpMethod({
      name: 'metafox/call/report',
      params: [callId, 'cancel']
    });
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* leaveCall(
  action: ItemLocalAction<{ callId: string }, { onSuccess: () => void }>
) {
  const { callId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.reportCall(callId, null, 'leave');

    action?.meta?.onSuccess && action?.meta?.onSuccess();
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* joinCall(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.reportCall(callId, null, 'join');
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

function* pingVoIpCall(action: ItemLocalAction<{ callId: string }>) {
  const { callId } = action.payload;
  const { chatplus } = yield* getGlobalContext();

  try {
    yield chatplus.pingVoIpCall(callId);
  } catch (error) {
    yield* handleActionErrorChat(error);
  }
}

const sagas = [
  takeEvery('chatplus/room/startVoiceCall', startVoiceCall),
  takeEvery('chatplus/room/startVideoChat', startVideoChat),
  takeLatest('chatplus/room/getCallInfo', getCallInfo),
  takeLatest('chatplus/room/getCallRoom', getCallRoom),
  takeLatest('chatplus/room/rejectCallFromPopup', rejectCallFromPopup),
  takeLatest('chatplus/room/acceptCallFromPopup', acceptCallFromPopup),
  takeLatest('chatplus/room/cancelCall', cancelCall),
  takeLatest('chatplus/room/leaveCall', leaveCall),
  takeLatest('chatplus/room/joinCall', joinCall),
  takeLatest('chatplus/room/pingVoIpCall', pingVoIpCall)
];

export default sagas;
