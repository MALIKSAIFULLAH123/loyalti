import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';

export default makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {},
      uiChatMsgSet: {},
      uiChatMsgSetAvatar: {},
      uiChatMsgSetBody: {},
      uiChatMsgItemMsg: {},
      uiChatMsgTypingIcon: {},
      uiChatMsgItem: {},
      uiChatMsgItemBodyOuter: {},
      uiChatMsgItemBodyInner: {},
      uiChatMsgItemBodyInnerWrapper: {},
      uiChatMsgSeenUsers: {},
      uiChatToolbar: {},
      uiChatItemBtn: {},
      uiChatIconBtn: {},
      uiChatMsgItemPin: {},
      uiChatMsgItemCall: {},
      uiChatMsgCallActionLink: {},
      uiChatMsgItemTime: {},
      uiChatMsgItemCallTitle: {},
      reactionEmoji: {},
      reactionCount: {}
    }),
  { name: 'ChatMessage' }
);
