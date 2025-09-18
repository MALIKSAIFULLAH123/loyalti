/**
 * @type: skeleton
 * name: sticker.itemView.itemOnStickerSet.skeleton
 */

import { styled, Skeleton } from '@mui/material';
import React from 'react';

const StickerPickerListItem = styled('li', {
  name: 'StickerPicker',
  slot: 'ListItem'
})(({ theme }) => ({
  display: 'inline-flex',
  marginBottom: theme.spacing(4),
  width: '25%',
  height: theme.spacing(11)
}));

export default function StickerItem({ identity }: Props) {

  return (
    <StickerPickerListItem
    >
      <Skeleton variant="rectangular" width="90%" height="100%" />
      
    </StickerPickerListItem>
  );
}
