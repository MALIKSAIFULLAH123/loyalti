import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentVideo';

const MediaWrapper = styled('figure', { name })(({ theme }) => ({
  margin: 0,
  display: 'block'
}));

const VideoStyled = styled('video', {
  name,
  slot: 'VideoStyled',
  shouldForwardProp: props => props !== 'isPageAllMessages'
})<{ isPageAllMessages?: boolean }>(({ theme, isPageAllMessages }) => ({
  width: '186px',
  maxWidth: '100%',
  display: 'inline-block',
  ...(isPageAllMessages && { width: '300px' })
}));
const VideoPreviewWrapper = styled('div', {
  name,
  slot: 'VideoPreviewWrapper',
  shouldForwardProp: props =>
    props !== 'isOwner' && props !== 'isPageAllMessages'
})<{ isOwner?: boolean; isPageAllMessages?: boolean }>(
  ({ theme, isOwner, isPageAllMessages }) => ({
    width: '144px',
    maxWidth: '100%',
    position: 'relative',
    cursor: 'pointer',
    ...(isOwner && { width: '186px' }),
    ...(isPageAllMessages && { width: '300px' })
  })
);
const VideoPreviewSrc = styled('span', { name, slot: 'VideoPreviewSrc' })(
  ({ theme }) => ({
    display: 'block',
    position: 'relative',
    backgroundSize: 'cover',
    backgroundPosition: 'center center',
    backgroundRepeat: 'no-repeat',
    backgroundOrigin: 'border-box',
    border: '1px solid rgba(0, 0, 0, 0.1)',
    '&:before': {
      content: '""',
      display: 'block',
      paddingBottom: '56.25%'
    }
  })
);

const CustomPlayButton = styled(LineIcon, {
  name,
  slot: 'CustomPlayButton'
})({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  margin: 'auto',
  fontSize: '40px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 2,
  background: 'rgba(0, 0, 0, 0.3)',
  color: '#fff',
  pointerEvents: 'none'
});
interface Props {
  video_url: string;
  thumb_url: string;
  video_type: any;
  isOwner?: boolean;
}
export default function MsgAttachmentVideo({
  video_url,
  thumb_url,
  isOwner
}: Props) {
  const { chatplus, usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;
  const [videoRender, setVideoRender] = React.useState<boolean>(false);

  if (!videoRender)
    return (
      <VideoPreviewWrapper
        isOwner={isOwner}
        isPageAllMessages={isPageAllMessages}
        onClick={() => setVideoRender(true)}
      >
        <VideoPreviewSrc
          style={{
            backgroundImage: `url(${thumb_url})`
          }}
        />
        <CustomPlayButton icon="ico-play-circle-o" />
      </VideoPreviewWrapper>
    );

  return (
    <MediaWrapper>
      <VideoStyled
        isPageAllMessages={isPageAllMessages}
        src={chatplus.sanitizeRemoteFileUrl(video_url)}
        controls
        autoPlay
      />
    </MediaWrapper>
  );
}
