/**
 * @type: skeleton
 * name: sticker.ui.stickerSet.skeleton
 */

import { Box, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const ImgItemView = styled(Box, { name: 'StickerPicker', slot: 'tabImg' })(
  ({ theme }) => ({
    height: theme.spacing(10),
    width: theme.spacing(10)
  })
);

const ItemView = styled(Box, { name: 'itemView' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(4, 1.5, 4, 2),
  borderBottom: `1px solid ${theme.palette.divider}`
}));

export default function LoadingSkeleton() {
  return (
    <ItemView>
      <ImgItemView>
        <Skeleton variant="rectangular" width="100%" height="100%" />
      </ImgItemView>
      <Box ml={2}>
        <Skeleton width={200} />
        <Skeleton width={100} />
      </Box>
    </ItemView>
  );
}
