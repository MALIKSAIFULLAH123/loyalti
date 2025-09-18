/**
 * @type: ui
 * name: sticker.ui.stickerSet
 */

import { useGetItem, useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { StickerSetShape } from '@metafox/sticker';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import { ButtonAction } from '@metafox/ui';

interface Props {
  identity: string;
}
const TabImg = styled('img', { name: 'StickerPicker', slot: 'tabImg' })(
  ({ theme }) => ({
    height: theme.spacing(10),
    maxWidth: theme.spacing(10)
  })
);

const ItemView = styled(Box, { name: 'itemView' })(({ theme }) => ({
  width: '100%',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  padding: theme.spacing(4, 1.5, 4, 2),
  borderBottom: `1px solid ${theme.palette.divider}`
}));

const InfoSticker = styled(Box, { name: 'infoSticker' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  flex: 1,
  marginRight: theme.spacing(1)
}));

const TitleSticker = styled(Box, { name: 'titleSticker' })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(18),
  marginBottom: theme.spacing(1.5),
  fontWeight: theme.typography.fontWeightSemiBold
}));

const TotalSticker = styled(Box, { name: 'totalSticker' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13)
}));

export default function StickerSet({ identity }: Props) {
  const { i18n, dialogBackend, dispatch } = useGlobal();
  const item = useGetItem<StickerSetShape>(identity);

  if (!item) return null;

  const { image, is_added, statistic, title } = item;

  const openStickerDetail = () => {
    dialogBackend.present({
      component: 'dialog.sticker.detail',
      props: {
        identity,
        addToMyList,
        removeToMyList
      }
    });
  };

  const addToMyList = onSuccess => {
    dispatch({
      type: 'sticker/addStickerSet',
      payload: { identity, onSuccess }
    });
  };

  const removeToMyList = onSuccess => {
    dispatch({
      type: 'sticker/removeStickerSet',
      payload: { identity, onSuccess }
    });
  };

  return (
    <ItemView>
      <InfoSticker onClick={openStickerDetail}>
        <TabImg
          draggable={false}
          alt="tabItem"
          src={getImageSrc(image, '200')}
        />
        <Box ml={3}>
          <TitleSticker>{title}</TitleSticker>
          <TotalSticker>
            {i18n.formatMessage(
              { id: 'total_stickers' },
              { value: statistic.total_sticker }
            )}
          </TotalSticker>
        </Box>
      </InfoSticker>
      {is_added ? (
        <ButtonAction variant="outlined" size="medium" action={removeToMyList}>
          {i18n.formatMessage({ id: 'remove' })}
        </ButtonAction>
      ) : (
        <ButtonAction variant="contained" size="medium" action={addToMyList}>
          {i18n.formatMessage({ id: 'add' })}
        </ButtonAction>
      )}
    </ItemView>
  );
}
