import { styled } from '@mui/material';
import React from 'react';

const UIChatMsgActions = styled('div', {
  name: 'uiChatMsgActions',
  slot: 'uiChatMsgActions'
})(({ theme }) => ({
  borderTop: theme.mixins.border('secondary'),
  paddingTop: theme.spacing(1),
  marginTop: theme.spacing(1),
  textAlign: 'center',
  justifyContent: 'center'
}));

export default function MsgActions({ children }) {
  return <UIChatMsgActions>{children}</UIChatMsgActions>;
}
