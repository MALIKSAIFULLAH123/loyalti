import { CLOSE_CALL_POPUP_DELAY } from '@metafox/chatplus/constants';
import { setLocalStatusCall } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React, { useEffect } from 'react';

const UICallView = styled('div', { slot: 'uiCallView' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  maxWidth: '100%',
  overflowX: 'hidden',
  boxSizing: 'border-box',
  position: 'fixed',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,

  backgroundColor: theme.palette.grey['800'],

  '& h1, h2, h3,h4, h5, h6': {
    color: 'white'
  }
}));

export default function EndedCallView({ callId }) {
  const { i18n } = useGlobal();

  useEffect(() => {
    setLocalStatusCall(callId, 'end');
  }, [callId]);

  useEffect(() => {
    setTimeout(window.close, CLOSE_CALL_POPUP_DELAY);
  }, []);

  return (
    <UICallView>
      <h1>{i18n.formatMessage({ id: 'the_call_is_ended' })}</h1>
    </UICallView>
  );
}
