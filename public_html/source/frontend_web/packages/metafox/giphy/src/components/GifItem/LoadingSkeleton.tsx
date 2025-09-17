/**
 * @type: skeleton
 * name: giphy.itemView.mainCard.skeleton
 */

import { Box, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const StickerPickerListItem = styled(Box, {
  name: 'StickerPicker',
  slot: 'ListItem'
})({
  display: 'inline-flex',
  width: '100%',
  height: '220px'
});

export default function LoadingSkeleton() {
  return (
    <StickerPickerListItem>
      <Skeleton variant="rectangular" width="100%" height="100%" />
    </StickerPickerListItem>
  );
}
