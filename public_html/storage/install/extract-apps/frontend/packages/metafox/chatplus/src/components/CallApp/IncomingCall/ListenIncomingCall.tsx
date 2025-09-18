import { useCalls } from '@metafox/chatplus/hooks';
import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import { useEffect } from 'react';

function ListenIncomingCall() {
  const { dispatch } = useGlobal();

  const calls = useCalls();

  // find First IncomingCall To Alert
  const callInfo =
    calls && Object.values(calls).find(call => call.callStatus === 'ringing');

  useEffect(() => {
    if (!isEmpty(callInfo)) {
      dispatch({
        type: 'chatplus/room/getCallInfo',
        payload: { callId: callInfo.callId }
      });
    }
  }, [callInfo, dispatch]);

  return null;
}

export default ListenIncomingCall;
