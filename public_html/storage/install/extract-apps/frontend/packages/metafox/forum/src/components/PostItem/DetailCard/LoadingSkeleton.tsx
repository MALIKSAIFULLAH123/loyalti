/**
 * @type: skeleton
 * name: forum_post.itemView.detailCard.skeleton
 */
import { ItemText, ItemView } from '@metafox/ui';
import { Skeleton, Box } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <Box width={'100%'}>
        <Box sx={{ display: 'flex' }}>
          <Box mr={2}>
            <Skeleton variant="circular" width={48} height={48} />
          </Box>
          <ItemText>
            <div>
              <Skeleton width={160} />
            </div>
            <div>
              <Skeleton width={200} />
            </div>
          </ItemText>
        </Box>
        <Box mt={1} mb={1}>
          <Skeleton width={'100%'} />
        </Box>
      </Box>
    </ItemView>
  );
}
