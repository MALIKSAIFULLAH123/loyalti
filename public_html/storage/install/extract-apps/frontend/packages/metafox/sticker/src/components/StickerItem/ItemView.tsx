
import { OnStickerClick } from '@metafox/sticker';
import React from 'react';
import StickerItem from '../StickerPicker/StickerItem';

interface Props {
  identity?: string;
  handleActionItem: OnStickerClick;
  data?: string[];
}

export default function StickerList({ identity, handleActionItem, data }: Props) {
  
  return (
    <StickerItem
      identity={identity}
      onStickerClick={handleActionItem}
    />
  );
}
