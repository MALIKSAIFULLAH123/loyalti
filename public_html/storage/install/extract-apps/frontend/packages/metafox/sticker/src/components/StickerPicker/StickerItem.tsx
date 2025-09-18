import { useGetItem, useGlobal } from '@metafox/framework';
import { OnStickerClick, StickerItemShape } from '@metafox/sticker/types';
import { styled } from '@mui/material/styles';
import React from 'react';

const StickerPickerListItem = styled('li', {
  name: 'StickerPicker',
  slot: 'ListItem'
})({
  display: 'inline-flex',
  margin: 5,
  width: 56,
  height: 56
});
const StickerPickerListImg = styled('img', {
  name: 'StickerPicker',
  slot: 'ListImg'
})({
  maxWidth: 56,
  maxHeight: 56
});

interface Props {
  onStickerClick: OnStickerClick;
  identity: string;
}

export default function StickerItem({ identity, onStickerClick }: Props) {
  const { isMobile } = useGlobal();
  const item = useGetItem<StickerItemShape>(identity);
  const [hover, setHover] = React.useState(false);

  if (!item) return;

  return (
    <StickerPickerListItem
      role="button"
      aria-label="sticker"
      data-testid="sticker"
      key={identity}
      onClick={() => onStickerClick(item.id)}
      onMouseOver={() => setHover(true)}
      onMouseLeave={() => setHover(false)}
    >
      <StickerPickerListImg
        draggable={false}
        alt={'sticker'}
        src={isMobile || hover ? item?.image : item?.thumbs?.origin}
      />
    </StickerPickerListItem>
  );
}
