import { styled } from '@mui/material';
import React from 'react';
import MsgAttachmentMedia from './MsgAttachmentMedia';

const name = 'MsgAttachmentMultiMedia';

const UIMsgImageAttachmentWrapper = styled('div', {
  name,
  slot: 'uiMsgImageAttachmentWrapper',
  shouldForwardProp: props => props !== 'isOwner'
})<{ isOwner?: boolean }>(({ theme, isOwner }) => ({
  display: 'flex',
  alignItems: 'center',
  flexWrap: 'wrap',
  justifyContent: isOwner ? 'flex-end' : 'flex-start',
  marginTop: theme.spacing(0.25),
  width: '100%',
  gap: theme.spacing(0.5)
}));

const UIMsgImageAttachmentItem = styled('div', {
  name,
  slot: 'uiMsgImageAttachmentItem'
})(({ theme }) => ({
  flexBasis: `calc(100% / 3 - ${theme.spacing(0.5)})`
}));

interface IProps {
  mediaItems: any[];
  isOwner: boolean;
  isOther?: boolean;
}
export default function MsgAttachmentMultiMedia({
  mediaItems,
  isOwner
}: IProps) {
  return (
    <UIMsgImageAttachmentWrapper isOwner={isOwner}>
      {mediaItems.map((item: any, i: number) => (
        <UIMsgImageAttachmentItem key={`k${i}`}>
          <MsgAttachmentMedia
            {...item}
            keyIndex={i}
            layout="multi-image"
            totalImages={mediaItems}
            isOwner={isOwner}
          />
        </UIMsgImageAttachmentItem>
      ))}
    </UIMsgImageAttachmentWrapper>
  );
}
