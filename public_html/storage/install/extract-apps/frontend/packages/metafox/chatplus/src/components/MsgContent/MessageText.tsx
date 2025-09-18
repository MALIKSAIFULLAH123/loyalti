/**
 * @type: ui
 * name: chatplus.messageContent.standard
 * chunkName: chatplusUI
 */

import formatTextMsg from '@metafox/chatplus/services/formatTextMsg';
import { MsgContentProps } from '@metafox/chatplus/types';
import { styled, Tooltip } from '@mui/material';
import { isEmpty } from 'lodash';
import React from 'react';
import MsgAttachments from '../MsgAttachments';
import MsgEmbeds from '../MsgEmbeds';
import MsgQuote from '../MsgQuote';
import { useChatRoom } from '@metafox/chatplus/hooks';
import {
  HIGHLIGHT_SEARCH_SECONDS,
  MODE_UN_SEARCH
} from '@metafox/chatplus/constants';

const name = 'MessageText';

const UIChatMsgItemMsg = styled('div', {
  name,
  slot: 'uiChatMsgItemMsg',
  shouldForwardProp: prop =>
    prop !== 'isOwner' &&
    prop !== 'isQuote' &&
    prop !== 'isMention' &&
    prop !== 'activeSearch' &&
    prop !== 'isMessageFilter'
})<{
  isOwner?: boolean;
  isQuote?: boolean;
  isMention?: any;
  activeSearch?: boolean;
  isMessageFilter?: boolean;
}>(({ theme, isOwner, isQuote, isMention, activeSearch, isMessageFilter }) => ({
  zIndex: 2,
  borderRadius: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(15),
  padding: theme.spacing(1.25),
  display: 'flex',
  alignItems: 'center',
  '& .mention': {
    fontWeight: 'bold'
  },
  backgroundColor: theme.palette.grey['100'],
  ...(theme.palette.mode === 'dark' && {
    backgroundColor: theme.palette.grey['600']
  }),
  overflowWrap: 'break-word',
  ...(!isMention && { maxWidth: '100%' }),
  '& div:first-of-type': {
    width: '100%'
  },
  '& a': {
    ...(!isMention && { maxWidth: '100%' }),
    overflowWrap: 'break-word',
    color: theme.palette.text.primary,
    textDecoration: 'underline',
    cursor: 'pointer',
    ...(isMention && {
      textDecoration: 'none',
      '&:hover': {
        textDecoration: 'underline'
      }
    })
  },
  ...(isOwner &&
    !isMessageFilter && {
      backgroundColor: theme.palette.primary.main,
      color: '#fff !important',
      '& a': {
        color: '#fff',
        textDecoration: 'underline'
      }
    }),
  ...(activeSearch && {
    '& div': {
      background: '#000',
      color: '#fff',
      '& a': {
        color: '#fff'
      }
    }
  })
}));

const UIChatMsgItemBodyInnerWrapper = styled('div', {
  name,
  slot: 'uiChatMsgItemBodyInnerWrapper',
  shouldForwardProp: prop =>
    prop !== 'isOwner' && prop !== 'isSearch' && prop !== 'isMessageFilter'
})<{ isOwner?: boolean; isSearch?: boolean; isMessageFilter?: boolean }>(
  ({ theme, isOwner, isSearch, isMessageFilter }) => ({
    width: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'flex-start',
    ...(!(isSearch || isMessageFilter) && isOwner && { alignItems: 'flex-end' })
  })
);

export const checkIsQuote = attachments => {
  const isQuote =
    attachments &&
    attachments.length &&
    attachments[attachments.length - 1]?.type !== 'file';

  if (isQuote) {
    const dataQuote = attachments.filter(item => item.message_id);

    return {
      isQuote,
      dataQuote: dataQuote ? dataQuote[0] : null
    };
  } else {
    return {
      isQuote,
      dataQuote: null
    };
  }
};

export default function MessageText({
  message,
  isOwner,
  createdDate,
  tooltipPosition,
  showActiveSearch = true,
  isMessageFilter = false,
  scrollMessage,
  isSearch = false
}: MsgContentProps) {
  const [highlight, setHighlight] = React.useState(false);
  const formattedMsg = message.msg
    ? formatTextMsg(message.msg, { mentions: message.mentions })
    : null;

  const { isQuote, dataQuote } = checkIsQuote(message?.attachments);

  const chatRoom = useChatRoom(message?.rid);

  const { msgSearch } = chatRoom || {};

  const activeSearch = React.useMemo(() => {
    if (
      !showActiveSearch ||
      isEmpty(msgSearch) ||
      msgSearch?.mode === MODE_UN_SEARCH
    )
      return false;

    let result = msgSearch?.id === message?.id;

    if (msgSearch?.mQuoteId === message?.id) {
      result = highlight;
    }

    return result;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    showActiveSearch,
    msgSearch?.mQuoteId,
    msgSearch?.id,
    msgSearch?.mode,
    message?.id,
    highlight
  ]);

  React.useEffect(() => {
    if (msgSearch?.mQuoteId === message?.id) {
      setHighlight(true);
      setTimeout(() => {
        setHighlight(false);
      }, HIGHLIGHT_SEARCH_SECONDS);
    }

    return () => {
      setHighlight(false);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [message?.id, msgSearch?.mQuoteId]);

  return (
    <Tooltip
      title={createdDate}
      placement={tooltipPosition}
      PopperProps={{
        variant: 'hidden-partview'
      }}
    >
      <UIChatMsgItemBodyInnerWrapper
        isOwner={isOwner}
        isSearch={isSearch}
        isMessageFilter={isMessageFilter}
      >
        {isQuote ? (
          <MsgQuote
            dataQuote={dataQuote}
            isOwner={isOwner}
            rid={message?.rid}
            scrollMessage={scrollMessage}
          />
        ) : null}
        {formattedMsg ? (
          <UIChatMsgItemMsg
            activeSearch={activeSearch}
            isMention={!isEmpty(message.mentions)}
            isQuote={isQuote}
            isOwner={isOwner}
            isMessageFilter={isMessageFilter}
          >
            <div dangerouslySetInnerHTML={{ __html: formattedMsg }} />
          </UIChatMsgItemMsg>
        ) : null}
        <MsgAttachments
          message={message}
          isOwner={isOwner}
          isQuote={isQuote}
          dataQuote={dataQuote}
        />
        <MsgEmbeds message={message} />
      </UIChatMsgItemBodyInnerWrapper>
    </Tooltip>
  );
}
