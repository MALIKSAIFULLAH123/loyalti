/**
 * @type: dialog
 * name: dialog.sticker.detail
 */

import { Dialog, DialogTitle, DialogContent } from '@metafox/dialog';
import { styled, Box } from '@mui/material';
import React from 'react';
import { StickerSetShape } from '@metafox/sticker';
import { useGetItem, useGlobal, useResourceAction } from '@metafox/framework';
import { compactData, getImageSrc } from '@metafox/utils';
import { ButtonAction } from '@metafox/ui';
import { STICKER } from '@metafox/sticker/constant';
import { ScrollContainer } from '@metafox/layout';

const DialogContentStyled = styled(DialogContent, {
  name: 'DialogContentStyled'
})(({ theme }) => ({
  padding: `${theme.spacing(0, 1, 0, 2)}!important`,
  height: '50vh'
}));

const TabImg = styled('img', { name: 'StickerPicker', slot: 'tabImg' })(
  ({ theme }) => ({
    height: theme.spacing(16),
    maxWidth: theme.spacing(20)
  })
);

const TitleSticker = styled(Box, { name: 'titleSticker' })(({ theme }) => ({
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(18),
  marginBottom: theme.spacing(1.5),
  fontWeight: theme.typography.fontWeightSemiBold
}));

const TotalSticker = styled(Box, { name: 'totalSticker' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13),
  marginBottom: theme.spacing(2)
}));

const InfoSticker = styled(Box, { name: 'infoSticker' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  borderBottom: `1px solid ${theme.palette.divider}`,
  paddingBottom: theme.spacing(2)
}));

export default function ManagerStickerDialog({
  identity,
  addToMyList,
  removeToMyList
}) {
  const stickerSet = useGetItem<StickerSetShape>(identity);
  const { i18n, useDialog, jsxBackend } = useGlobal();
  const ListView = jsxBackend.get('core.block.listview');
  const { dialogProps } = useDialog();
  const dataSourceByStickerSet = useResourceAction(
    STICKER,
    STICKER,
    'viewByStickerSet'
  );

  if (!stickerSet) return;

  const { image, title, statistic, is_added } = stickerSet;

  const gridItemProps = {
    xs: 3,
    md: 3,
    sm: 3,
    xl: 3,
    lg: 3
  };

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle enableBack>
        {i18n.formatMessage({ id: 'sticker_detail' })}
      </DialogTitle>
      <DialogContentStyled variant="fitScroll">
        <InfoSticker>
          <TabImg
            draggable={false}
            alt={title}
            src={getImageSrc(image, '200')}
          />
          <Box ml={2} mt={2}>
            <TitleSticker>{title}</TitleSticker>
            <TotalSticker>
              {i18n.formatMessage(
                { id: 'total_stickers' },
                { value: statistic.total_sticker }
              )}
            </TotalSticker>
            {is_added ? (
              <ButtonAction
                variant="outlined"
                size="medium"
                action={removeToMyList}
              >
                {i18n.formatMessage({ id: 'remove' })}
              </ButtonAction>
            ) : (
              <ButtonAction
                variant="contained"
                size="medium"
                action={addToMyList}
              >
                {i18n.formatMessage({ id: 'add' })}
              </ButtonAction>
            )}
          </Box>
        </InfoSticker>

        <ScrollContainer autoHeightMax={'100%'} autoHide autoHeight>
          <Box
            ml={{ xs: 0, sm: 5 }}
            mt={2}
            sx={{ display: 'flex', flexWrap: 'wrap' }}
          >
            <ListView
              dataSource={{
                apiUrl: dataSourceByStickerSet.apiUrl,
                apiParams: compactData(dataSourceByStickerSet.apiParams, {
                  id: stickerSet.id
                })
              }}
              emptyPage="hide"
              errorPage="hide"
              itemView={'sticker.itemView.itemOnStickerSet'}
              canLoadMore
              canLoadSmooth
              numberOfItemsPerPage={20}
              gridContainerProps={{
                spacing: 0
              }}
              gridItemProps={gridItemProps}
            />
          </Box>
        </ScrollContainer>
      </DialogContentStyled>
    </Dialog>
  );
}
