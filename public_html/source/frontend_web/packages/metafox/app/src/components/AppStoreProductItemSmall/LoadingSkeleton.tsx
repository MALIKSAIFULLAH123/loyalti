/**
 * @type: skeleton
 * name: app_store_product.itemView.smallCard.skeleton
 */
import { ImageSkeleton, ItemMedia, ItemText, ItemView } from '@metafox/ui';
import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton(props) {
  return (
    <ItemView {...props}>
      <Box sx={{ width: '100%' }}>
        <Box
          sx={{
            display: 'flex',
            alignItems: 'flex-start',
            justifyContent: 'space-between'
          }}
        >
          <ItemMedia>
            <ImageSkeleton ratio="11" />
          </ItemMedia>
          <Box
            sx={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'flex-end'
            }}
          >
            <Skeleton width={'70px'} />
            <Skeleton width={'50px'} />
          </Box>
        </Box>
        <Box mt={1}>
          <ItemText>
            <Skeleton width={'90%'} />
            <Skeleton width={'60%'} />
          </ItemText>
          <Skeleton width={'100%'} />
        </Box>
      </Box>
    </ItemView>
  );
}
