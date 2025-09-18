/**
 * @type: ui
 * name: livestreaming.itemView.modalCard
 */
import { useGlobal } from '@metafox/framework';
import { LivestreamItemShape } from '@metafox/livestreaming/types';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { styled, Box } from '@mui/material';
import * as React from 'react';
import { getImageSrc } from '@metafox/utils';

type LiveItemModalViewProps = {
  item: LivestreamItemShape;
  hideActionMenu?: boolean;
  onMinimizePhoto: (minimize: boolean) => void;
};

const name = 'VideoItemModalView';

const ActionBar = styled('div', {
  name,
  slot: 'actionBar',
  shouldForwardProp: props => props !== 'isNativeControl'
})<{ isNativeControl?: boolean }>(({ theme, isNativeControl }) => ({
  position: 'absolute',
  right: 0,
  top: isNativeControl ? 32 : 0,
  width: '100%',
  padding: theme.spacing(1),
  display: 'flex',
  justifyContent: 'space-between',
  zIndex: 1,
  alignItems: 'center'
}));

export default function VideoItemModalView({
  identity,
  item,
  onMinimizePhoto,
  actions
}: LiveItemModalViewProps) {
  const { jsxBackend } = useGlobal();

  const LiveLabel = jsxBackend.get('livestreaming.ui.labelLive');
  const FlyReaction = jsxBackend.get('livestreaming.ui.flyReaction');
  const ViewerLabel = jsxBackend.get('livestreaming.ui.labelViewer');
  const LiveVideoPlayer = jsxBackend.get('livestreaming.ui.liveVideoPlayer');

  if (!item) return null;

  const { is_streaming, stream_key, thumbnail_url, _live_watching } =
    item || {};
  const cover = getImageSrc(thumbnail_url, '500', '');

  const isNativeControl = false;

  return (
    <Box sx={{ height: '100%', position: 'relative' }}>
      {is_streaming || _live_watching ? (
        <LiveVideoPlayer item={item} dialog actions={actions} />
      ) : (
        <VideoPlayer
          src={item.video_url || item.destination || null}
          thumb_url={cover}
          autoPlay
          isNativeControl={isNativeControl}
        />
      )}
      {FlyReaction ? (
        <FlyReaction streamKey={item?.stream_key} identity={identity} />
      ) : null}
      <ActionBar isNativeControl={isNativeControl}>
        <Box sx={{ display: 'inline-flex', alignItems: 'center' }}>
          {is_streaming && LiveLabel ? (
            <Box mx={1}>
              <LiveLabel />
            </Box>
          ) : null}
          {is_streaming && ViewerLabel ? (
            <ViewerLabel streamKey={stream_key} />
          ) : null}
        </Box>
      </ActionBar>
    </Box>
  );
}
