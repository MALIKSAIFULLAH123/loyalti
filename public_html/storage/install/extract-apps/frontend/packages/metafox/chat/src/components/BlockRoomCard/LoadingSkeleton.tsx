/**
 * @type: skeleton
 * name: blocked.itemView.chatroomCard.skeleton
 */

import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton, styled } from '@mui/material';
import React from 'react';

const StyledRoot = styled(ItemView)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  height: 72
}));

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <StyledRoot
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid="skeleton_roomcard"
    >
      <ItemMedia>
        <Skeleton variant="avatar" width={40} height={40} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width={'100%'} />
        </ItemTitle>
        <ItemTitle>
          <Skeleton width={120} />
        </ItemTitle>
      </ItemText>
    </StyledRoot>
  );
}
