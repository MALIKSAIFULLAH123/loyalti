/**
 * @type: skeleton
 * name: story.itemView.storyCard.skeleton
 * chunkName: story
 */
import { ImageSkeleton } from '@metafox/ui';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'StorySkeletonMainCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  width: '140px',
  height: '250px',
  marginRight: theme.spacing(1),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  background: theme.palette.background.paper,
  '&:last-child': {
    marginRight: 0
  }
}));

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  height: '100%',
  width: '100%'
}));

const ItemMediaStyled = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  padding: theme.spacing(1.5)
}));
const ItemTitleStyled = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  bottom: 0,
  padding: theme.spacing(1.5)
}));

export default function LoadingSkeleton() {
  return (
    <ItemViewStyled data-testid="story-skeleton-main-card">
      <WrapperImage>
        <ImageSkeleton style={{ height: '100%' }} />
      </WrapperImage>
      <ItemMediaStyled>
        <Skeleton variant="avatar" width={42} height={42} />
      </ItemMediaStyled>
      <ItemTitleStyled>
        <Skeleton width={120} />
      </ItemTitleStyled>
    </ItemViewStyled>
  );
}
