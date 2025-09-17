/**
 * @type: skeleton
 * name: friend.itemView.birthdayWidthAvatar.skeleton
 */
import {
  ItemMedia
} from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <Box p={1}>
      <ItemMedia>
        <Skeleton variant="circular" width={80} height={80} />
      </ItemMedia>
    </Box>
  );
}
