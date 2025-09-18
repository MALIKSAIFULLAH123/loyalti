import { useGlobal } from '@metafox/framework';
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
  slot: 'uiMsgImageAttachmentItem',
  shouldForwardProp: props =>
    props !== 'isPageAllMessages' &&
    props !== 'isLastItem' &&
    props !== 'isAudio' &&
    props !== 'totalImages'
})<{
  isPageAllMessages?: boolean;
  isLastItem?: boolean;
  isAudio?: boolean;
  totalImages?: number;
}>(({ theme, isPageAllMessages, isLastItem, isAudio, totalImages }) => ({
  flexBasis: `calc(100% / 3 - ${theme.spacing(0.5)})`,
  ...(isAudio && {
    width: '185px'
  }),
  ...(totalImages === 2 && {
    flexBasis: `calc(100% / 2 - ${theme.spacing(0.5)})`
  })
}));

interface Props {
  mediaItems: any;
  isOwner: boolean;
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  isOther?: boolean;
}
export default function MsgAttachmentMultiMedia({
  mediaItems,
  isOwner,
  msgType,
  isOther
}: Props) {
  const { usePageParams } = useGlobal();
  const pageParams = usePageParams();

  const isPageAllMessages = pageParams?.rid || false;

  return (
    <UIMsgImageAttachmentWrapper isOwner={isOwner}>
      {mediaItems.map((item, i) => (
        <UIMsgImageAttachmentItem
          isPageAllMessages={isPageAllMessages}
          isLastItem={i === mediaItems.length - 1}
          isAudio={!!item?.audio_url}
          key={`k${i}`}
          totalImages={mediaItems.length}
        >
          <MsgAttachmentMedia
            {...item}
            layout={'multi-image'}
            keyIndex={i}
            totalImages={mediaItems}
            isOwner={isOwner}
            msgType={msgType}
            isOther={isOther}
          />
        </UIMsgImageAttachmentItem>
      ))}
    </UIMsgImageAttachmentWrapper>
  );
}
