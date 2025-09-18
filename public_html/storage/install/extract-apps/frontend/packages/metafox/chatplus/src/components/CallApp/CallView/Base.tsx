import { CLOSE_CALL_POPUP_DELAY } from '@metafox/chatplus/constants';
import { useCallItem, useSessionUser } from '@metafox/chatplus/hooks';
import { estimateCallAction } from '@metafox/chatplus/utils';
import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useEffect, useState } from 'react';
import ConnectingView from './ConnectingView';
import EndedCallView from './EndedCallView';
import IncomingView from './IncomingView';
import JoinCallView from './JoinCallView';
import OutgoingView from './OutgoingView';

export interface Props extends BlockViewProps {}

const name = 'CallViewBlock';
const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'block',
  maxWidth: '100%',
  overflowX: 'hidden',
  boxSizing: 'border-box',
  position: 'fixed',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0
}));

const WrapperViewCall = styled(Box, { name, slot: 'WrapperViewCall' })(
  ({ theme }) => ({})
);

export default function Base(props: Props) {
  const { dispatch, usePageParams, i18n } = useGlobal();

  const { callId } = usePageParams();
  const [ended, setEnded] = useState(false);
  const [isConnected, setConnected] = useState(false);

  const callInfo = useCallItem(callId);
  const userSession = useSessionUser();

  useEffect(() => {
    if (!isEmpty(callInfo)) {
      setConnected(true);

      if (window) {
        window.document.title = i18n.formatMessage({
          id: callInfo?.subject || 'calling'
        });
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [callId, callInfo]);

  const cancelCall = () => {
    dispatch({
      type: 'chatplus/room/cancelCall',
      payload: { callId }
    });
    dispatch({
      type: 'chatplus/callInfo',
      payload: { callId, callStatus: 'end' }
    });
  };

  const closeWindow = () => setTimeout(window.close, CLOSE_CALL_POPUP_DELAY);

  const readyToClose = () => {
    document.querySelector('#meet').innerHTML = '';
    setEnded(true);
    dispatch({
      type: 'chatplus/room/leaveCall',
      payload: { callId },
      meta: { onSuccess: () => closeWindow() }
    });
  };

  window.onunload = () => {
    alert('is unloaded');
    localStorage.removeItem('chatplus/callId');
    localStorage.removeItem(callId);
  };

  const callAction = estimateCallAction(callId, callInfo?.callStatus);

  if (!isConnected) return <ConnectingView />;

  return (
    <Root id="meet">
      <WrapperViewCall>
        {!ended && callAction === 'invite' ? (
          <OutgoingView
            callId={callId}
            callInfo={callInfo}
            cancelCall={cancelCall}
          />
        ) : null}
        {!ended && callAction === 'ringing' ? (
          <IncomingView
            callId={callId}
            callInfo={callInfo}
            cancelCall={cancelCall}
          />
        ) : null}
        {ended || callAction === 'end' ? (
          <EndedCallView callId={callId} />
        ) : null}
        {!ended && callAction === 'start' ? (
          <JoinCallView
            callId={callId}
            callInfo={callInfo}
            user={userSession}
            readyToClose={readyToClose}
          />
        ) : null}
        {!ended && callAction === 'nothing' ? <ConnectingView /> : null}
      </WrapperViewCall>
    </Root>
  );
}
