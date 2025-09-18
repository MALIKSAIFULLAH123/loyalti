/**
 * @type: skeleton
 * name: sticker.ui.stickerList.skeleton
 */

import { Skeleton, styled } from '@mui/material';
import { range } from 'lodash';
import * as React from 'react';

const StickerListContent = styled('ul', {
  name: 'StickerPicker',
  slot: 'ListContent'
})({
  listStyle: 'none none outside',
  margin: 4,
  padding: 0,
  width: 264
});

const StickerPickerListItem = styled('li', {
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
    <StickerListContent>
      {range(0, 16).map(index => (
        <StickerPickerListItem key={index}>
          <Skeleton variant="rectangular" width="100%" height="100%" />
        </StickerPickerListItem>
      ))}
    </StickerListContent>
  );
}
