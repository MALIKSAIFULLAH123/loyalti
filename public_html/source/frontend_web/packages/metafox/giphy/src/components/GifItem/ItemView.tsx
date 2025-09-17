import { useGetItem } from '@metafox/framework';
import { OnGifClick, GiphyItemShape } from '@metafox/giphy';
import { styled } from '@mui/material/styles';
import { get } from 'lodash';
import React from 'react';

const name = 'GiphyPicker';
const GiphyPickerItem = styled('div', {
  name,
  slot: 'ListItem'
})({
  display: 'inline-flex',
  width: '100%',
  height: '100%',
  minHeight: '50px'
});
const GiphyPickerImg = styled('img', {
  name,
  slot: 'ListImg'
})({
  maxWidth: '100%',
  maxHeight: '100%',
  width: '100%'
});
interface Props {
  handleActionItem: OnGifClick;
  identity: string;
}

export default function GiphyItem(props: Props) {
  const { identity, handleActionItem } = props;
  const item = useGetItem<GiphyItemShape>(identity);

  if (!item) return;

  const imageSrc = get(item?.images, 'fixed_width.url');

  return (
    <GiphyPickerItem
      role="button"
      aria-label="giphy"
      data-testid="giphy"
      key={identity}
      onClick={() => handleActionItem(item?.giphy_gif_id)}
    >
      <GiphyPickerImg alt={'gif'} src={imageSrc} />
    </GiphyPickerItem>
  );
}
