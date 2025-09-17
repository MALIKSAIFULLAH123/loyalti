/**
 * @type: ui
 * name: photo.block.EmptyPhotoAlbum
 * title: Photo Album Is Empty
 * keywords: no content
 */
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material/styles';
import * as React from 'react';

const name = 'EmptyPhotoAlbum';

const Root = styled('div', { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center',
  alignItems: 'center'
}));
const HeaderInfo = styled('div', { name, slot: 'headerInfo' })(({ theme }) => ({
  marginBottom: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(24),
  color: theme.palette.text.primary,
  fontWeight: theme.typography.fontWeightSemiBold
}));
const StatusRoot = styled('div', { name, slot: 'statusRoot' })(({ theme }) => ({
  marginBottom: theme.spacing(2),
  fontSize: theme.mixins.pxToRem(18),
  color: theme.palette.text.hint
}));

const EmptyPhotoAlbum = ({ isVisible = false }) => {
  const { i18n, jsxBackend, getSetting } = useGlobal();

  const UploadItem = jsxBackend.get('photo_album.itemView.AddPhotoAlbumDetail');
  const canUploadPhoto = getSetting(
    'photo.photo_allow_uploading_video_to_photo_album'
  );
  const canUploadVideo = getSetting('video');

  return (
    <Root>
      <HeaderInfo>
        {i18n.formatMessage({ id: 'no_all_photo_found' })}
      </HeaderInfo>
      <StatusRoot>
        {i18n.formatMessage({
          id: canUploadPhoto && canUploadVideo 
            ? 'album_has_no_photos_videos_uploaded'
            : 'album_has_no_photos_uploaded'
        })}
      </StatusRoot>
      {isVisible && <UploadItem />}
    </Root>
  );
};
export default EmptyPhotoAlbum;
