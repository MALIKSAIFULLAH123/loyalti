import { useGlobal } from '@metafox/framework';
import { MediaViewPhotoProps } from '@metafox/photo/types';
import { LineIcon } from '@metafox/ui';
import { styled, Box } from '@mui/material';
import * as React from 'react';

const name = 'PhotoViewSet';

const WrapperVideo = styled('div', { name, slot: 'WrapperVideo' })(
  ({ theme }) => ({
    position: 'relative',
    backgroundColor: '#000',
    width: '100%',
    overflow: 'hidden',
    '& iframe': {
      width: '100%',
      height: '100%'
    }
  })
);

const WrapperImage = styled('div', { name, slot: 'WrapperImage' })(
  ({ theme }) => ({
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    backgroundColor: '#000',
    width: '100%',
    justifyContent: 'center',
    position: 'relative'
  })
);

const ControlPhotoIcon = styled('div', {
  name,
  slot: 'nextPhoto',
  shouldForwardProp: prop => prop !== 'displayPosition'
})<{
  displayPosition: 'left' | 'right';
}>(({ theme, displayPosition }) => ({
  left: displayPosition === 'left' ? theme.spacing(2) : 'unset',
  right: displayPosition === 'right' ? theme.spacing(2) : 'unset',
  display: 'flex',
  alignItems: 'center',
  backgroundColor: 'rgba(0,0,0,0.4)',
  justifyContent: 'center',
  position: 'absolute',
  zIndex: 1,
  padding: '10px',
  borderRadius: '50%',
  width: '30px',
  height: '30px',
  cursor: 'pointer',
  color: '#FFF',
  top: '50%',
  transform: 'translateY(-50%)'
}));

export default function PhotoViewSet(props: MediaViewPhotoProps) {
  const {
    identity,
    nextUrl,
    prevUrl,
    mediaType,
    onAddPhotoTag,
    onRemovePhotoTag,
    onMinimizePhoto,
    onArrowClick,
    sx,
    sxWrapper
  } = props;

  const { jsxBackend, useGetItem } = useGlobal();
  const item = useGetItem(identity);

  const PhotoItemModalView = jsxBackend.get('photo.itemView.modalCard');

  const VideoItemModalView = jsxBackend.get('video.itemView.modalCard');

  if (!item) return null;

  return (
    <Box sx={{ width: '100%', display: 'flex', ...sx }}>
      {mediaType === 'photo' || !VideoItemModalView ? (
        <WrapperImage sx={sxWrapper}>
          <>
            {nextUrl ? (
              <ControlPhotoIcon
                onClick={() => onArrowClick(nextUrl)}
                displayPosition="right"
              >
                <LineIcon icon="ico-angle-right" />
              </ControlPhotoIcon>
            ) : null}
            {prevUrl ? (
              <ControlPhotoIcon
                onClick={() => onArrowClick(prevUrl)}
                displayPosition="left"
              >
                <LineIcon icon="ico-angle-left" />
              </ControlPhotoIcon>
            ) : null}
            {React.createElement(PhotoItemModalView, {
              item,
              identity,
              taggedFriends: [],
              onAddPhotoTag,
              onRemovePhotoTag,
              onMinimizePhoto
            })}
          </>
        </WrapperImage>
      ) : (
        <WrapperVideo sx={sxWrapper}>
          <>
            {nextUrl ? (
              <ControlPhotoIcon
                onClick={() => onArrowClick(nextUrl)}
                displayPosition="right"
              >
                <LineIcon icon="ico-angle-right" />
              </ControlPhotoIcon>
            ) : null}
            {prevUrl ? (
              <ControlPhotoIcon
                onClick={() => onArrowClick(prevUrl)}
                displayPosition="left"
              >
                <LineIcon icon="ico-angle-left" />
              </ControlPhotoIcon>
            ) : null}
            {React.createElement(VideoItemModalView, {
              item,
              onMinimizePhoto
            })}
          </>
        </WrapperVideo>
      )}
    </Box>
  );
}
