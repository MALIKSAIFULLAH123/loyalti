/**
 * @type: ui
 * name: chat.messageContent.text
 */

import formatTextMsg from '@metafox/chat/services/formatTextMsg';
import { MsgContentProps } from '@metafox/chat/types';
import { styled, Tooltip } from '@mui/material';
import React from 'react';
import MsgAttachments from '../MsgAttachments';
// import MsgEmbeds from '../MsgEmbeds';
import MsgQuote from '../MsgQuote';
import { useChatRoom } from '@metafox/chat/hooks';
import { MODE_UN_SEARCH } from '@metafox/chat/constants';

const name = 'MessageText';

const UIChatMsgItemMsg = styled('div', {
  name,
  slot: 'uiChatMsgItemMsg',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isQuote' && prop !== 'activeSearch'
})<{ isOwner?: boolean; isQuote?: boolean; activeSearch?: boolean }>(
  ({ theme, isOwner, isQuote, activeSearch }) => ({
    borderRadius: theme.spacing(1),
    fontSize: theme.mixins.pxToRem(15),
    padding: theme.spacing(1.25),
    backgroundColor: theme.palette.grey['100'],
    ...(theme.palette.mode === 'dark' && {
      backgroundColor: theme.palette.grey['600']
    }),
    overflowWrap: 'break-word',
    maxWidth: '100%',
    zIndex: 2,
    '& a': {
      color: isOwner ? '#fff' : theme.palette.text.primary,
      textDecoration: 'underline',
      cursor: 'pointer',
      overflowWrap: 'break-word'
    },
    ...(isOwner && {
      backgroundColor: theme.palette.primary.main,
      color: '#fff !important'
    }),
    ...(activeSearch && {
      background: '#000',
      color: '#fff'
    })
  })
);

const UIChatMsgItemBodyInnerWrapper = styled('div', {
  name,
  slot: 'uiChatMsgItemBodyInnerWrapper',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'filter' && prop !== 'isSearch'
})<{ isOwner?: boolean; filter?: boolean; isSearch?: boolean }>(
  ({ theme, isOwner, filter, isSearch }) => ({
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'flex-start',
    ...(!isSearch && isOwner && !filter && { alignItems: 'flex-end' })
  })
);

export default function MessageText({
  message,
  isOwner,
  createdDate,
  tooltipPosition,
  isSearch = false,
  showActiveSearch = true
}: MsgContentProps) {
  const formattedMsg = message.message
    ? formatTextMsg(message.message, { mentions: message.mentions })
    : null;

  const dataQuote = message?.extra;

  const chatRoom = useChatRoom(message?.room_id);

  const { msgSearch } = chatRoom || {};

  const activeSearch =
    showActiveSearch &&
    msgSearch &&
    (msgSearch?.mQuoteId === message?.id || msgSearch?.id === message?.id) &&
    msgSearch?.mode !== MODE_UN_SEARCH;

  return (
    <Tooltip
      title={createdDate}
      placement={tooltipPosition}
      PopperProps={{
        disablePortal: true
      }}
    >
      <UIChatMsgItemBodyInnerWrapper
        isOwner={isOwner}
        filter={message?.filtered}
        isSearch={isSearch}
      >
        {dataQuote ? (
          <MsgQuote
            dataQuote={dataQuote}
            isOwner={isOwner}
            isSearch={isSearch}
          />
        ) : null}
        {formattedMsg ? (
          <UIChatMsgItemMsg
            activeSearch={activeSearch}
            isQuote={Boolean(dataQuote)}
            isOwner={isOwner}
            className={'uiChatMsgItemMsg'}
            dangerouslySetInnerHTML={{ __html: formattedMsg }}
          />
        ) : null}
        <MsgAttachments message={message} isOwner={isOwner} />
        {/* <MsgEmbeds message={message} /> */}
      </UIChatMsgItemBodyInnerWrapper>
    </Tooltip>
  );
}
