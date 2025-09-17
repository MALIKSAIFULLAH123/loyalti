import { useGetItem } from '@metafox/framework';
import { PhotoItemProps } from '@metafox/photo/types';
import { Image, ItemView } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Radio, styled } from '@mui/material';
import React from 'react';

const name = 'SelectPhotoProfile-ItemView';

const RootStyled = styled('div', { name, slot: 'root' })(({ theme }) => ({
  minWidth: 130,
  cursor: 'pointer',
  transition: 'all 0.2s ease',
  position: 'relative',
  '&:hover': {
    boxShadow: theme.shadows[4],
    opacity: 0.8
  },
  minHeight: 150
}));
const SelectPhotoStyled = styled('div', { name, slot: 'selectPhoto' })(
  ({ theme }) => ({
    position: 'absolute',
    top: theme.spacing(0),
    left: theme.spacing(0),
    background: 'rgba(0, 0, 0, 0.2)'
  })
);
const RadioStyled = styled(Radio, { name, slot: 'Radio' })(({ theme }) => ({
  color: 'white',
  '&.Mui-checked': {
    color: 'white'
  }
}));

const SelectPhotoProfile = ({
  item,
  user,
  identity,
  handleAction,
  state,
  itemProps,
  wrapAs,
  wrapProps
}: PhotoItemProps) => {
  const cover = getImageSrc(item.image);
  const { fileItems, files, onSuccess, albumId, isDetailAlbum } = itemProps;

  if (!item) return null;

  let fileSelected = fileItems;

  if (isDetailAlbum) {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    const photos = useGetItem(`photo.entities.photo_album.${albumId}.photos`);

    if (photos && photos.length) {
      fileSelected = photos.map(x => {
        // eslint-disable-next-line react-hooks/rules-of-hooks
        const item = useGetItem(x);

        return item;
      });
    }
  }

  const isDuplicationItem = Boolean(fileSelected.find(x => x.id === item.id));

  if (isDuplicationItem) return null;

  const valueChecked = Boolean(files.find(x => x.id === item.id));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <RootStyled onClick={() => onSuccess(item)}>
        <Image src={cover} aspectRatio={'43'} alt={item.title} />
        <SelectPhotoStyled>
          <RadioStyled checked={valueChecked} sx={{}} />
        </SelectPhotoStyled>
      </RootStyled>
    </ItemView>
  );
};

export default SelectPhotoProfile;
