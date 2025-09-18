/**
 * @type: skeleton
 * name: story.itemView.storyAvatarCard.skeleton
 */
import { SIZE_AVATAR_TYPE } from '@metafox/story/constants';
import { Box, Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'StorySkeletonAvatarCard';

const ItemViewStyled = styled(Box, { name, slot: 'itemview' })(({ theme }) => ({
  marginRight: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  position: 'relative',
  '&:hover': {
    cursor: 'pointer'
  },
  '&:last-child': {
    marginRight: 0
  }
}));

const ItemMediaStyled = styled(Box, { name })(({ theme }) => ({
  marginBottom: theme.spacing(1)
}));
const ItemTitleStyled = styled(Box, { name })(({ theme }) => ({
  width: '100%',
  textAlign: 'center'
}));

export default function LoadingSkeleton() {
  return (
    <ItemViewStyled
      data-testid="story-skeleton-main-card"
      maxWidth={SIZE_AVATAR_TYPE}
    >
      <ItemMediaStyled>
        <Skeleton
          variant="avatar"
          width={SIZE_AVATAR_TYPE}
          height={SIZE_AVATAR_TYPE}
        />
      </ItemMediaStyled>
      <ItemTitleStyled>
        <Skeleton width={70} />
      </ItemTitleStyled>
    </ItemViewStyled>
  );
}
