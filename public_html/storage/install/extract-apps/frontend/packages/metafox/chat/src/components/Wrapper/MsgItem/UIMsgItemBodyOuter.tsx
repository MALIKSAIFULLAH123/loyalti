import { styled } from '@mui/material';
import React from 'react';
import { MsgItemBodyOuterProps } from '../type';

const name = 'UIMsgItemBodyOuter';

const RootStyled = styled('div', {
  name,
  slot: 'RootStyled',
  shouldForwardProp: prop =>
    prop !== 'isPageAllMessages' &&
    prop !== 'msgType' &&
    prop !== 'isMsgEdit' &&
    prop !== 'totalImage' &&
    prop !== 'totalImageQuote' &&
    prop !== 'msgContentType'
})<{
  isPageAllMessages?: boolean;
  msgType?: string;
  isMsgEdit?: boolean;
  totalImage?: number;
  totalImageQuote?: number;
  msgContentType?: string;
}>(
  ({
    theme,
    isPageAllMessages,
    msgType,
    isMsgEdit,
    totalImage,
    totalImageQuote,
    msgContentType
  }) => ({
    minWidth: 0,

    maxWidth: `calc(100% - ${isMsgEdit ? '80px' : '50px'})`,
    ...(totalImage > 1 && { width: '75%' }),
    ...(totalImage === 1 && { width: '65%' }),
    ...((msgType === 'message_pinned' || msgType === 'message_unpinned') && {
      width: '100%'
    }),
    ...(msgContentType === 'messageDeleted' && {
      maxWidth: 'calc(100% - 26px)'
    }),
    // isPageAllMessages
    ...(isPageAllMessages && {
      maxWidth: '65%',
      ...((msgType === 'message_pinned' || msgType === 'message_unpinned') && {
        maxWidth: totalImageQuote === 1 ? '300px' : '400px',
        width: '100%'
      }),
      ...(totalImage === 1 && {
        maxWidth: '300px',
        width: '100%'
      }),
      ...(totalImage > 1 && { width: '55%' }),

      // mobile
      [theme.breakpoints.down('sm')]: {
        maxWidth: `calc(100% - ${isMsgEdit ? '80px' : '50px'})`,
        ...(totalImage > 1 && { width: '75%' }),
        ...(totalImage === 1 && { width: '60%' }),
        ...((msgType === 'message_pinned' ||
          msgType === 'message_unpinned') && {
          width: '75%'
        })
      }
    }),
    ...(msgType === 'messageDeleted' && {
      width: 'auto'
    })
  })
);

export default function UIMsgItemBodyOuter({
  children,
  isMsgEdit = false,
  totalImage,
  isPageAllMessages,
  msgType,
  msgContentType,
  totalImageQuote
}: MsgItemBodyOuterProps) {
  if (!children) return;

  return (
    <RootStyled
      isMsgEdit={isMsgEdit}
      msgContentType={msgContentType}
      totalImage={totalImage}
      isPageAllMessages={isPageAllMessages}
      msgType={msgType}
      totalImageQuote={totalImageQuote}
    >
      {children}
    </RootStyled>
  );
}
