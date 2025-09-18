/**
 * @type: itemView
 * name: sticker.itemView.itemOnStickerSet
 * chunkName: sticker
 */

import { useGetItem } from '@metafox/framework';
import { StickerItemShape } from '@metafox/sticker/types';
import { styled } from '@mui/material/styles';
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
const StickerPickerListImg = styled('img', {
  name: 'StickerPicker',
  slot: 'ListImg'
})(({ theme }) => ({
  maxWidth: theme.spacing(13)
}));

export default function StickerItem({ identity }: Props) {
  const item = useGetItem<StickerItemShape>(identity);
  const [hover, setHover] = React.useState(false);

  return (
    <StickerPickerListItem
      role="button"
      aria-label="sticker"
      data-testid="sticker"
      key={identity}
    >
      <StickerPickerListImg
        draggable={false}
        alt={'sticker'}
        src={hover ? item?.image : item?.thumbs?.origin}
        onMouseOver={() => setHover(true)}
        onMouseLeave={() => setHover(false)}
      />
    </StickerPickerListItem>
  );
}
