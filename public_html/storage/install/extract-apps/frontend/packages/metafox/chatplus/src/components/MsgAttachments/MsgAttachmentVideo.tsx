import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';
import VideoPlayerChat from '../VideoPlayerChat';

const name = 'MsgAttachmentVideo';

const MediaWrapper = styled('figure', { name })(({ theme }) => ({
  margin: 0,
  display: 'block'
}));

interface Props {
  video_url: string;
  thumb_url: string;
  video_type: any;
  isOwner?: boolean;
  allowOpenPreview?: boolean;
  video_width?: number;
  video_height?: number;
}

function MsgAttachmentVideo({
  video_url,
  video_width,
  video_height,
  thumb_url,
  isOwner,
  allowOpenPreview = true
}: Props) {
  const { chatplus, usePageParams, useIsMobile } = useGlobal();

  const isMobile = useIsMobile();
  const idPlaying = `msg-video-${video_url}`;

  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const widthVideoDefault = !isMobile && isPageAllMessages ? 300 : 186;
  const ratio =
    video_width <= widthVideoDefault
      ? widthVideoDefault
      : video_width / widthVideoDefault;
  const height =
    video_width <= widthVideoDefault ? 'auto' : video_height / ratio;

  return (
    <MediaWrapper>
      <VideoPlayerChat
        width={widthVideoDefault}
        height={height as number}
        src={chatplus.sanitizeRemoteFileUrl(video_url)}
        idPlaying={idPlaying}
        thumb_url={thumb_url}
        allowOpenPreview={allowOpenPreview}
      />
    </MediaWrapper>
  );
}

export default React.memo(
  MsgAttachmentVideo,
  (prevProps, nextProps) =>
    prevProps?.video_url === nextProps?.video_url &&
    prevProps?.allowOpenPreview === nextProps?.allowOpenPreview
);
