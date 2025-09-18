import { UIMsgAttachImgWrapper } from '../Wrapper/MsgAttachmentImg';
import { useGlobal } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentImage';

const ImageStyled = styled('img', { name })(({ theme }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  width: '100%',
  borderRadius: theme.spacing(1),
  border: theme.mixins.border('secondary')
}));
interface Props {
  keyIndex?: string;
  isOwner?: boolean;
  image: any;
  download_url?: string;
  file_name?: string;
  id?: string;
  allowOpenPreview?: boolean;
}

const loadImage = (setImageDimensions, imageUrl) => {
  const img = new Image();
  img.src = imageUrl;

  img.onload = () => {
    setImageDimensions({
      height: img.height,
      width: img.width
    });
  };
  img.onerror = err => {
    setImageDimensions(err);
  };
};

export default function MsgAttachmentImage({
  isOwner,
  image,
  download_url,
  file_name,
  id,
  allowOpenPreview = true
}: Props) {
  const { dispatch, usePageParams, assetUrl } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const [imageDimensions, setImageDimensions] = React.useState(null);

  const imgSrc = getImageSrc(image, '500', assetUrl('photo.no_image'));
  const imgOriginSrc = getImageSrc(image, 'origin', assetUrl('photo.no_image'));

  React.useEffect(() => {
    if (imgSrc) {
      loadImage(setImageDimensions, imgSrc);
    }
  }, [imgSrc]);

  const ratioImage = imageDimensions
    ? imageDimensions.height / imageDimensions.width
    : false;

  const presentImageView = () => {
    if (!allowOpenPreview) return;

    dispatch({
      type: 'chat/room/presentImageView',
      payload: {
        image: {
          id: 'img0',
          src: imgOriginSrc,
          download_url,
          is_image: true,
          file_name,
          imageId: id
        }
      }
    });
  };

  return (
    <UIMsgAttachImgWrapper
      isPageAllMessages={isPageAllMessages}
      ratioImage={ratioImage}
      isOwner={isOwner}
    >
      <ImageStyled src={imgSrc} onClick={presentImageView} alt={file_name} />
    </UIMsgAttachImgWrapper>
  );
}
