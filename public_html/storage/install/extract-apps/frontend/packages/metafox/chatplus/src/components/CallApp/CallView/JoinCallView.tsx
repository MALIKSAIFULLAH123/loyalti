import injectCallFrame from '@metafox/chatplus/services/injectCallFrame';
import { setLocalStatusCall } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import React, { useEffect } from 'react';

interface IProps {
  callId: any;
  callInfo: any;
  user: any;
  readyToClose: () => void;
}

export default function JoinCallView({
  callId,
  callInfo,
  user,
  readyToClose
}: IProps) {
  const { dispatch, i18n } = useGlobal();

  const ping = () =>
    dispatch({
      type: 'chatplus/room/pingVoIpCall',
      payload: { callId }
    });

  useEffect(() => {
    setLocalStatusCall(callId, 'start');
    window.setInterval(ping, 5000);
    dispatch({
      type: 'chatplus/room/joinCall',
      payload: { callId }
    });
  }, []);

  useEffect(() => {
    if (callInfo && user && readyToClose) {
      injectCallFrame(
        {
          ...callInfo,
          subjectTitle: callInfo?.group
            ? callInfo?.subject
            : i18n.formatMessage({ id: 'calling' })
        },
        user,
        readyToClose
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return <div />;
}
