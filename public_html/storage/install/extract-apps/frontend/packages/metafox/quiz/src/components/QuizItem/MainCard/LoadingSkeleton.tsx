/**
 * @type: skeleton
 * name: quiz.itemView.mainCard.skeleton
 */
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="11" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <Box>
          <Skeleton width={160} />
        </Box>
        <Box mt={2}>
          <Skeleton width={160} />
        </Box>
      </ItemText>
    </ItemView>
  );
}
