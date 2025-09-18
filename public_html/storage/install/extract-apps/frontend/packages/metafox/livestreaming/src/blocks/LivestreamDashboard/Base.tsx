import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { LivestreamDetailViewProps } from '../../types';
import { Box } from '@mui/material';

function LivestreamDetail({
  item,
  identity,
  state,
  handleAction,
  actions
}: LivestreamDetailViewProps) {
  const { ItemDetailInteraction, jsxBackend } = useGlobal();
  const LiveVideoPlayer = jsxBackend.get('livestreaming.ui.liveVideoPlayer');
  const LiveLabel = jsxBackend.get('livestreaming.ui.labelLive');
  const FlyReaction = jsxBackend.get('livestreaming.ui.flyReaction');
  const ViewerLabel = jsxBackend.get('livestreaming.ui.labelViewer');

  if (!item) return null;

  const PendingCard = jsxBackend.get('core.itemView.pendingReviewCard');

  const { is_streaming, stream_key, is_pending } = item;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box mb={-2}>
          <Box m={-2}>
            <LiveVideoPlayer item={item} actions={actions} dashboard />
            <Box
              sx={{
                display: 'inline-flex',
                alignItems: 'center',
                position: 'absolute',
                top: 8,
                left: 8
              }}
            >
              {is_streaming && LiveLabel ? (
                <Box mx={1}>
                  <LiveLabel />
                </Box>
              ) : null}
              {is_streaming && ViewerLabel ? (
                <ViewerLabel streamKey={stream_key} />
              ) : null}
            </Box>
            {FlyReaction ? (
              <FlyReaction streamKey={item?.stream_key} identity={identity} />
            ) : null}
          </Box>
          <ItemDetailInteraction
            identity={identity}
            state={state}
            handleAction={handleAction}
            forceHideCommentList
            hideComposerInListComment
            forceHideReactionGroup={!item?.is_streaming}
          />
        </Box>
        {is_pending && PendingCard ? (
          <PendingCard sxWrapper={{ mt: 3 }} item={item} />
        ) : null}
      </BlockContent>
    </Block>
  );
}

export default LivestreamDetail;
