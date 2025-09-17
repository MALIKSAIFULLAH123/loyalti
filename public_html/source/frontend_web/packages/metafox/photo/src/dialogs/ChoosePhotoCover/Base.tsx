import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import {
  useGlobal,
  useResourceAction,
  RemoteDataSource
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { IconButton, Tab, Tabs, useMediaQuery } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import React from 'react';
import {
  ALBUMS_TAB,
  CHOOSE_FROM_ALBUM,
  CHOOSE_PHOTO_ALBUM,
  LATEST_PHOTOS_TAB
} from './constant';
import useStyles from './styles';
import {
  APP_PHOTO,
  RESOURCE_PHOTO,
  RESOURCE_ALBUM
} from '@metafox/photo/constant';

const gridContainerProps = { spacing: 1 };
const gridItemProps = { xs: 12, sm: 6, md: 4, lg: 4, xl: 4 };

type DataSourceType = RemoteDataSource | {};

export default function ChooseCoverPhoto({ identity, onSuccess, onFailure }) {
  const { useDialog, i18n, jsxBackend, compactData } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const classes = useStyles();
  const PhotoListing = jsxBackend.get('photo.block.photoListingBlock');

  const [tab, setTab] = React.useState<number>(LATEST_PHOTOS_TAB);
  const [step, setStep] = React.useState<number>(CHOOSE_PHOTO_ALBUM);
  const [name, setName] = React.useState<string>('');
  const [albumId, setAlbumId] = React.useState<string>('');
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const dataSourcePhotoConfig: DataSourceType =
    useResourceAction(APP_PHOTO, RESOURCE_PHOTO, 'chooseAvatarCoverPhoto') ||
    {};
  const dataSourcePhotoFromAlbumConfig: DataSourceType =
    useResourceAction(
      APP_PHOTO,
      RESOURCE_PHOTO,
      'chooseAvatarCoverPhotoFromAlbum'
    ) || {};
  const dataSourceAlbumConfig: DataSourceType = useResourceAction(
    APP_PHOTO,
    RESOURCE_ALBUM,
    'chooseAvatarCoverAlbum'
  ) || { apiUrl: '/photo-album' };

  const resource_name = identity.split('.')[0];
  const id = identity.split('.')[3];

  const itemPhotoProps = {
    close: closeDialog,
    onSuccess,
    onFailure,
    resource_name
  };

  const itemAlbumProps = {
    setId: id => setAlbumId(id),
    setStep: step => setStep(step),
    setName: name => setName(name)
  };

  const photoPagingId = `setCover/photo/${resource_name}/${id}`;

  const dataPhotoSource = {
    ...dataSourcePhotoConfig,
    apiParams: compactData(dataSourcePhotoConfig.apiParams, { user_id: id })
  };

  const albumPagingId = `setCover/album/${resource_name}/${id}`;
  const dataAlbumSource = {
    ...dataSourceAlbumConfig,
    apiParams: compactData(dataSourceAlbumConfig.apiParams, { user_id: id })
  };

  const albumPhotoPagingId = `setCover/photo-album/${resource_name}/${albumId}/${id}`;

  const dataAlbumPhotoSource = {
    ...dataSourcePhotoFromAlbumConfig,
    apiParams: compactData(dataSourcePhotoFromAlbumConfig.apiParams, {
      album_id: albumId
    })
  };

  const changeTab = (_, value) => {
    setTab(value);
  };

  return (
    <Dialog
      {...dialogProps}
      maxWidth={false}
      fullWidth
      className={classes.dialog}
    >
      <DialogTitle className={classes.dialogTitle}>
        {step === CHOOSE_FROM_ALBUM && (
          <IconButton size="small" className={classes.btnBack}>
            <LineIcon
              icon="ico-arrow-left"
              onClick={() => setStep(CHOOSE_PHOTO_ALBUM)}
            />
          </IconButton>
        )}
        {step === CHOOSE_PHOTO_ALBUM
          ? i18n.formatMessage({ id: 'choose_cover_photo' })
          : name}
      </DialogTitle>
      {step === CHOOSE_PHOTO_ALBUM && (
        <DialogContent
          className={classes.dialogContent}
          sx={{
            overflow: 'hidden !important'
          }}
        >
          <Tabs
            value={tab}
            onChange={changeTab}
            indicatorColor="primary"
            textColor="primary"
          >
            <Tab
              className={classes.tab}
              disableRipple
              label={i18n.formatMessage({ id: 'latest_photos' })}
            />
            <Tab
              className={classes.tab}
              disableRipple
              label={i18n.formatMessage({ id: 'albums' })}
            />
          </Tabs>
          {tab === LATEST_PHOTOS_TAB && (
            <ScrollContainer
              className={classes.scroll}
              autoHide
              autoHeight
              autoHeightMax={isSmallScreen ? '90vh' : 352}
              style={{ height: 'auto' }}
            >
              <PhotoListing
                canLoadMore
                canLoadSmooth
                clearDataOnUnMount
                dataSource={dataPhotoSource}
                pagingId={photoPagingId}
                itemView={'photo.itemView.choosePhoto'}
                emptyPage="core.block.no_content"
                emptyPageProps={{ title: 'no_all_photo_found' }}
                gridContainerProps={gridContainerProps}
                gridItemProps={gridItemProps}
                itemProps={itemPhotoProps}
                blockLayout="Profile - Clean Body Only"
              />
            </ScrollContainer>
          )}
          {tab === ALBUMS_TAB && (
            <ScrollContainer
              className={classes.scroll}
              autoHide
              autoHeight
              autoHeightMax={isSmallScreen ? '90vh' : 352}
              style={{ height: 'auto' }}
            >
              <PhotoListing
                canLoadMore
                canLoadSmooth
                dataSource={dataAlbumSource}
                clearDataOnUnMount
                pagingId={albumPagingId}
                emptyPage="core.block.no_content"
                emptyPageProps={{ title: 'no_albums_found' }}
                itemView={'photo_album.itemView.chooseCoverPhoto'}
                gridContainerProps={gridContainerProps}
                gridItemProps={gridItemProps}
                itemProps={itemAlbumProps}
                blockLayout="Profile - Clean Body Only"
              />
            </ScrollContainer>
          )}
        </DialogContent>
      )}
      {step === CHOOSE_FROM_ALBUM && (
        <DialogContent
          className={classes.dialogContent}
          sx={{
            overflow: 'hidden !important'
          }}
        >
          <ScrollContainer
            className={classes.scrollAlbum}
            autoHide
            autoHeight
            autoHeightMax={isSmallScreen ? '90vh' : 400}
            style={{ height: 'auto' }}
          >
            <PhotoListing
              canLoadMore
              clearDataOnUnMount
              dataSource={dataAlbumPhotoSource}
              pagingId={albumPhotoPagingId}
              emptyPage="core.block.no_content"
              itemView={'photo.itemView.choosePhoto'}
              gridContainerProps={gridContainerProps}
              gridItemProps={gridItemProps}
              itemProps={itemPhotoProps}
              blockLayout="Profile - Clean Body Only"
              emptyPageProps={{ title: 'no_all_photo_found' }}
            />
          </ScrollContainer>
        </DialogContent>
      )}
    </Dialog>
  );
}
