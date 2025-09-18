import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentAudio';

const UIChatAudioCustom = styled('div', {
  name,
  slot: 'uiChatAudioCustom',
  shouldForwardProp: props =>
    props !== 'isPageAllMessages' &&
    props !== 'msgType' &&
    props !== 'isOwner' &&
    props !== 'isOther'
})<{
  isPageAllMessages?: boolean;
  msgType?: string;
  isOwner?: boolean;
  isOther?: boolean;
}>(({ theme, isPageAllMessages, msgType, isOwner, isOther }) => ({
  width: '300px',
  maxWidth: '100%',
  ...((msgType === 'message_pinned' ||
    msgType === 'message_unpinned' ||
    isOther) && {
    width: isOwner ? '177px' : '137px'
  }),
  ...((msgType === 'message_pinned' ||
    msgType === 'message_unpinned' ||
    isOther) &&
    isPageAllMessages && {
      width: '200px'
    })
}));

interface Props {
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  isOwner?: boolean;
  audio_url: string;
  isOther?: boolean;
  audio_duration?: number;
}

function MsgAttachmentAudio({
  audio_url,
  msgType,
  isOwner,
  isOther,
  audio_duration
}: Props) {
  const { usePageParams, jsxBackend } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  return (
    <UIChatAudioCustom
      isPageAllMessages={isPageAllMessages}
      msgType={msgType}
      isOwner={isOwner}
      isOther={isOther}
    >
      {jsxBackend.render({
        component: 'chatplus.ui.messageWaveForm',
        props: {
          autoPlay: false,
          url: audio_url,
          isOwner,
          audioDuration: audio_duration,
          isPageAllMessages
        }
      })}
    </UIChatAudioCustom>
  );
}

export default React.memo(
  MsgAttachmentAudio,
  (prevProps, nextProps) => prevProps?.audio_url === nextProps?.audio_url
);
