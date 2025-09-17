import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { useMediaQuery, useTheme } from '@mui/material';
import produce from 'immer';
import React from 'react';
import useStyles from './styles';

const gridContainerProps = { spacing: 1 };
const gridItemProps = { xs: 12, sm: 6, md: 4, lg: 4, xl: 4 };

function SelectPhotoProfile({
  fileItems,
  uploadFiles,
  setFiles,
  isDetailAlbum,
  albumId,
  dataSource
}) {
  const { jsxBackend } = useGlobal();
  const classes = useStyles();
  const theme = useTheme();

  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  const PhotoListing = jsxBackend.get('photo.block.photoListingBlock');

  const onSuccess = item => {
    const valueChecked = Boolean(uploadFiles.find(x => x.id === item.id));

    setFiles(
      produce(uploadFiles, draft => {
        if (valueChecked) {
          const itemIndex = uploadFiles.findIndex(x => x.id === item.id);
          draft.splice(itemIndex, 1);
        } else {
          draft.push(item);
        }
      })
    );
  };

  const itemPhotoProps = {
    fileItems,
    isDetailAlbum,
    albumId,
    onSuccess,
    files: uploadFiles
  };

  return (
    <ScrollContainer
      className={classes.scroll}
      autoHide
      autoHeight
      autoHeightMax={isSmallScreen ? '90vh' : 352}
    >
      <PhotoListing
        canLoadMore
        canLoadSmooth
        clearDataOnUnMount
        dataSource={dataSource}
        itemView="photo.itemView.selectPhotoProfile"
        emptyPage="core.block.no_content"
        emptyPageProps={{
          title: 'no_photos_found'
        }}
        gridContainerProps={gridContainerProps}
        gridItemProps={gridItemProps}
        itemProps={itemPhotoProps}
        blockLayout="Profile - Clean Body Only"
        itemLayout="Photo - Main Card - Listings"
      />
    </ScrollContainer>
  );
}

export default SelectPhotoProfile;
