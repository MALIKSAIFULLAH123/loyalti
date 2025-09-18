/**
 * @type: skeleton
 * name: sticker.itemView.mainCard.skeleton
 */

import { Box, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const StickerPickerListItem = styled(Box, {
  name: 'StickerPicker',
  slot: 'ListItem'
})({
  display: 'inline-flex',
  margin: 5,
  width: 56,
  height: 56
});

export default function LoadingSkeleton() {
  return (
    <StickerPickerListItem>
      <Skeleton variant="rectangular" width="100%" height="100%" />
    </StickerPickerListItem>
  );
}
