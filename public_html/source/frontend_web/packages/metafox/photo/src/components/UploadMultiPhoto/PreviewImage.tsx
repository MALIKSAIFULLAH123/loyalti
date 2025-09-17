import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';

const PreviewImage = styled('div', {
  name: 'MultipleUploadField',
  slot: 'PreviewImg',
  shouldForwardProp: props => props !== 'radio'
})<{ radio?: string }>(({ theme, radio }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  right: 0,
  bottom: 0,
  backgroundColor: theme.palette.grey['A700'],
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'auto 100%',
  maxWidth: '100%',
  ...(radio &&
    radio === 'horizontal' && {
      backgroundSize: 'cover'
    })
}));

const PreviewImageComponent = ({ item }) => {
  const [imageDimensions, setImageDimensions] = React.useState<any>({});

  const loadImage = (setImageDimensions, imageUrl) => {
    try {
      const img = new Image();
      img.src = imageUrl;

      img.onload = () => {
        setImageDimensions({
          height: img.height,
          width: img.width
        });
      };
      img.onerror = err => {
        // err
        // console.error(err);
      };
    } catch (err) {
      // err
    }
  };

  const imageUrl =
    item?.source || item.image_url || getImageSrc(item?.image, '1024');

  React.useEffect(() => {
    loadImage(setImageDimensions, imageUrl);
  }, []);

  let radio = 'horizontal';
  radio =
    imageDimensions && imageDimensions.width / imageDimensions.height <= 1
      ? 'vertical'
      : 'horizontal';

  return (
    <PreviewImage
      radio={radio}
      style={{
        backgroundImage: `url(${imageUrl})`
      }}
    />
  );
};

export default PreviewImageComponent;
