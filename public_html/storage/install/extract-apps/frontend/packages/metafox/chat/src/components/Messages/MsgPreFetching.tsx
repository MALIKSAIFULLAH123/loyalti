import formatTextMsg from '@metafox/chat/services/formatTextMsg';
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import MsgQuote from '../MsgQuote';
import { useGlobal } from '@metafox/framework';

interface MessageSetProps {
  item: any;
}

const name = 'MsgSet';

const UIChatMsg = styled('div', {
  name,
  slot: 'uiChatMsgSet'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row-reverse',
  padding: theme.spacing(0, 2),
  opacity: 0.6,
  pointerEvents: 'none'
}));

const UIChatMsgSetBody = styled('div', {
  name,
  slot: 'uiChatMsgSetBody',
  shouldForwardProp: prop => prop !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  flex: 1,
  minWidth: 0,
  ...(isOwner && { flexDirection: 'row-reverse' })
}));

const UIChatMsgItemMsg = styled('div', {
  name,
  slot: 'uiChatMsgItemMsg',
  shouldForwardProp: prop => prop !== 'isOwner' && prop !== 'isQuote'
})<{ isOwner?: boolean; isQuote?: boolean }>(({ theme, isOwner, isQuote }) => ({
  zIndex: 2,
  minHeight: theme.spacing(5),
  marginBottom: theme.spacing(0.5),
  borderRadius: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(15),
  padding: theme.spacing(1.25),
  backgroundColor: theme.palette.grey['100'],
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['600']
  }),
  overflowWrap: 'break-word',
  '& a': {
    color: isOwner ? '#fff' : theme.palette.text.primary,
    textDecoration: 'underline',
    cursor: 'pointer',
    overflowWrap: 'break-word'
  },
  ...(isOwner && {
    backgroundColor: theme.palette.primary.main,
    color: '#fff !important'
  })
}));

const UIChatMsgItemBodyInnerWrapper = styled('div', {
  name,
  slot: 'uiChatMsgItemBodyInnerWrapper'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end'
}));

export default function MsgPreFetching({ item }: MessageSetProps) {
  const { useGetItems } = useGlobal();

  if (!item.text && !item.dataQuote) return null;

  let quoteAttachments = item?.dataQuote?.attachments;

  if (quoteAttachments && typeof quoteAttachments?.[0] === 'string') {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    quoteAttachments = useGetItems(item?.dataQuote?.attachments);
  }

  return (
    <UIChatMsg>
      <UIChatMsgSetBody>
        <UIChatMsgItemBodyInnerWrapper>
          {!isEmpty(item.dataQuote) ? (
            <MsgQuote
              dataQuote={{ ...item.dataQuote, attachments: quoteAttachments }}
              isOwner
            />
          ) : null}
          {item.text ? (
            <UIChatMsgItemMsg
              isQuote={false}
              isOwner
              className={'uiChatMsgItemMsg'}
              dangerouslySetInnerHTML={{ __html: formatTextMsg(item.text) }}
            />
          ) : null}
        </UIChatMsgItemBodyInnerWrapper>
      </UIChatMsgSetBody>
    </UIChatMsg>
  );
}
