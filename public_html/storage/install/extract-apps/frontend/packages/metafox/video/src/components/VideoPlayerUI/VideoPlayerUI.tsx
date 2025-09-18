/**
 * @type: ui
 * name: video.itemView.modalCard
 */
import { VideoItemShape } from '@metafox/video';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { Box } from '@mui/material';
import * as React from 'react';
import { styled } from '@mui/material/styles';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const MediaStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: theme.mixins.pxToRem(16),
  height: '100%',
  width: '100%',
  color: '#fff',
  aspectRatio: '16 / 9'
}));

type VideoItemModalViewProps = {
  item: VideoItemShape;
  hideActionMenu?: boolean;
  isNativeControl?: boolean;
  isCountVideo?: boolean; 
};

export default function VideoPlayerUI({
  item,
  isNativeControl = false,
  isCountVideo = true
}: VideoItemModalViewProps) {
  const { i18n } = useGlobal();

  if (!item) return null;

  return (
    <Box sx={{ height: '100%', width: '100%' }}>
      {item?.is_failed ? (
        <MediaStyled>
          <LineIcon icon="ico-warning" sx={{ fontSize: '24px' }} />
          <Box mt={1}>
            {i18n.formatMessage({ id: 'video_has_been_processed_failed' })}
          </Box>
        </MediaStyled>
      ) : (
        <VideoPlayer
          src={item.video_url || item.destination || null}
          embed_code={item.embed_code}
          thumb_url={item.image}
          autoPlay
          isModalPlayer
          identity={item?._identity}
          isNativeControl={isNativeControl}
          isCountVideo={isCountVideo}
          id={item?.id}
        />
      )}
    </Box>
  );
}
