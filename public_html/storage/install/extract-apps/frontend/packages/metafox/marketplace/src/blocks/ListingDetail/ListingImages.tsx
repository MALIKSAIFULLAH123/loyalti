import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Box } from '@mui/material';
import transform from 'lodash/transform';
import React, { useCallback, useMemo, useState } from 'react';
import ImageGallery from 'react-image-gallery';
import 'react-image-gallery/styles/css/image-gallery.css';
import { ListingImageItemShape } from '@metafox/marketplace/types';
import { getImageSrc } from '@metafox/utils';

const name = 'ListingImages';

const ImageGalleryWrapper = styled(Box, { name, slot: 'root' })(
  ({ theme }) => ({
    position: 'relative',
    width: '100%',
    '& .image-gallery .image-gallery-thumbnail': {
      width: 73,
      height: 73,
      border: 'none',
      borderRadius: theme.shape.borderRadius / 2,
      marginRight: theme.spacing(1),
      minWidth: 73
    },
    '& .image-gallery .image-gallery-thumbnail.active': {
      backgroundColor: theme.palette.primary.main,
      padding: 2
    },
    '& .image-gallery .image-gallery-thumbnail.active $ThumbBgOverplay': {
      opacity: 0
    },
    '& .image-gallery-thumbnails .image-gallery-thumbnails-container': {
      display: 'flex'
    },
    '& .image-gallery .image-gallery-thumbnail + .image-gallery-thumbnail': {
      marginLeft: 0
    },
    '& .image-gallery .image-gallery-thumbnails': {
      padding: theme.spacing(1, 0)
    },
    '& .image-gallery-slides': {
      borderRadius: theme.shape.borderRadius / 2
    },
    '& .image-gallery-slide': {
      backfaceVisibility: 'hidden'
    },
    [theme.breakpoints.down('xs')]: {
      width: '100%',
      '& .image-gallery-thumbnails-wrapper': {
        display: 'none'
      },
      '& .image-gallery-slides': {
        borderRadius: 0
      },
      '& $ImageMain': {
        borderRadius: 0
      }
    }
  })
);
const ImageMain = styled('div', { name, slot: 'imageMain' })(({ theme }) => ({
  width: '100%',
  height: 400,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: '#000',
  borderRadius: theme.shape.borderRadius / 2,
  overflow: 'hidden',
  [theme.breakpoints.down('xs')]: {
    height: 'auto',
    paddingTop: '100%',
    position: 'relative'
  }
}));
const ImageBox = styled('div', { name, slot: 'imageBox' })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));
const ImageThumb = styled('div', { name, slot: 'imageThumb' })(({ theme }) => ({
  backgroundPosition: 'center',
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  cursor: 'pointer',
  position: 'relative',
  overflow: 'hidden'
}));
const ThumbBg = styled('span', { name, slot: 'thumbBg' })(({ theme }) => ({
  display: 'block',
  paddingTop: '100%',
  backgroundPosition: 'center',
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  cursor: 'pointer',
  borderRadius: theme.shape.borderRadius / 2
}));
const ThumbBgOverplay = styled('span', { name, slot: 'thumbBgOverplay' })(
  ({ theme }) => ({
    backgroundColor: theme.palette.background.default,
    position: 'absolute',
    top: 0,
    right: 0,
    left: 0,
    bottom: 0,
    opacity: 0.5,
    '.active &': {
      opacity: 0
    }
  })
);
const NavButton = styled(Button, {
  name,
  slot: 'navButton',
  shouldForwardProp: prop => prop !== 'navDirection'
})<{ navDirection: 'left' | 'right' }>(({ theme, navDirection }) => ({
  width: theme.spacing(4),
  height: theme.spacing(4),
  backgroundColor: '#fff',
  borderRadius: theme.spacing(4),
  color: '#616161',
  fontSize: theme.mixins.pxToRem(13),
  textAlign: 'center',
  lineHeight: 32,
  position: 'absolute',
  transform: 'translate(0, -50%)',
  top: '50%',
  zIndex: 1,
  border: 'none',
  display: 'flex',
  cursor: 'pointer',
  minWidth: 32,
  '&:hover': {
    backgroundColor: '#f0f0f0'
  },
  ...(navDirection === 'left' && {
    left: theme.spacing(1)
  }),
  ...(navDirection === 'right' && {
    right: theme.spacing(1)
  })
}));

function ListingImages({ images: idsImages, alt }) {
  const { dialogBackend, useGetItems } = useGlobal();
  const [currentIndex, setCurrentIndex] = useState(0);
  const images = useGetItems<ListingImageItemShape>(idsImages);
  const [disableKeyDown, setDisableKeyDown] = React.useState(false);

  const renderItem = useCallback(
    image => (
      <ImageMain>
        <ImageBox>
          <img
            style={{ width: '100%', height: '100%', objectFit: 'contain' }}
            alt={image.originalAlt}
            src={image.original}
          />
        </ImageBox>
      </ImageMain>
    ),
    []
  );

  const renderThumbInner = useCallback(image => {
    return (
      <ImageThumb>
        <ThumbBg
          style={{ backgroundImage: `url(${image.thumbnail})` }}
        ></ThumbBg>
        <ThumbBgOverplay />
      </ImageThumb>
    );
  }, []);

  const renderLeftNav = (onClick, disabled) => {
    return (
      <NavButton navDirection="left" disabled={disabled} onClick={onClick}>
        <LineIcon icon={'ico-angle-left'} />
      </NavButton>
    );
  };

  const renderRightNav = (onClick, disabled) => {
    return (
      <NavButton navDirection="right" disabled={disabled} onClick={onClick}>
        <LineIcon icon={'ico-angle-right'} />
      </NavButton>
    );
  };

  // useMemo for avoid rendering ImageGallery cause bug
  // (auto back first Image when change state open, currentIndex  )
  const renderImages = useMemo(
    () =>
      transform(
        images,
        (result, image) => {
          const newItem = {
            original: getImageSrc(image?.image, '400'),
            thumbnail: getImageSrc(image?.image, '240'),
            renderItem,
            renderThumbInner,
            originalAlt: alt
          };
          result.push(newItem);
        },
        []
      ),
    [alt, images, renderItem, renderThumbInner]
  );

  const onImageLoad = () => {
    return <div>Loading</div>;
  };

  const handleClick = () => {
    setDisableKeyDown(true);
    dialogBackend
      .present({
        component: 'marketplace.dialog.photoGallery',
        props: { startIndex: currentIndex, images: idsImages, alt }
      })
      .then(() => {
        setDisableKeyDown(false);
      });
  };

  const handleSlide = currentIndex => {
    setCurrentIndex(currentIndex);
  };

  if (!images) return null;

  return (
    <ImageGalleryWrapper>
      <ImageGallery
        items={renderImages}
        showFullscreenButton={false}
        showPlayButton={false}
        onSlide={handleSlide}
        onImageLoad={onImageLoad}
        renderLeftNav={renderLeftNav}
        renderRightNav={renderRightNav}
        onClick={handleClick}
        disableKeyDown={disableKeyDown}
        swipeConfig={{ trackMouse: true }}
      />
    </ImageGalleryWrapper>
  );
}

export default ListingImages;
