/**
 * @type: skeleton
 * name: forum_search.itemView.mainCard.skeleton
 */
import { ItemText, ItemView, ItemTitle } from '@metafox/ui';
import { Skeleton, Box } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <Box mr={1.5}>
        <Skeleton variant="circular" width={48} height={48} />
      </Box>
      <ItemText>
        <div>
          <Skeleton width={160} />
        </div>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
        <div>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}
