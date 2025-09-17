/**
 * @type: skeleton
 * name: photo.itemView.selectPhotoProfile.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Skeleton, styled } from '@mui/material';
import React from 'react';

const name = 'SelectPhotoProfile-Skeleton';

const SkeletonStyled = styled(Skeleton, { name, slot: 'Skeleton' })(
  ({ theme }) => ({
    minWidth: 130,
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    position: 'relative',
    '&:hover': {
      boxShadow: theme.shadows[4],
      opacity: 0.8
    },
    minHeight: 150
  })
);

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid={name}>
      <SkeletonStyled variant="rectangular" />
    </ItemView>
  );
}
