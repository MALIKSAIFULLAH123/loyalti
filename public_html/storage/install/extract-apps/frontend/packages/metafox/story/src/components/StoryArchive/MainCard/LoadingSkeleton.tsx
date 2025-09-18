/**
 * @type: skeleton
 * name: story.itemView.storyArchiveCard.skeleton
 */
import { STORY_IMAGE_RADIO } from '@metafox/story/constants';
import { ImageSkeleton, ItemView } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'StorySkeletonMainCard';

const WrapperImage = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'height'
})<{ height?: number }>(({ theme, height }) => ({
  width: '100%',
  paddingBottom: `${STORY_IMAGE_RADIO * 100}%`,
  '&:hover': {
    cursor: 'pointer'
  }
}));

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid="story-archive-card"
      data-testid="story-skeleton-main-card"
    >
      <WrapperImage>
        <ImageSkeleton
          style={{ height: '100%', width: '100%', position: 'absolute' }}
        />
      </WrapperImage>
    </ItemView>
  );
}
