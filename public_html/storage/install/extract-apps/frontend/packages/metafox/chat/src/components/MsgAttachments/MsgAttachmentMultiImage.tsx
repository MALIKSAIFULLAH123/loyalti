import { UIMsgAttachMultiImgWrapper } from '../Wrapper/MsgAttachmentImg';
import { useGlobal } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentImage';

const ImageStyled = styled('img', {
  name
})(({ theme }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  width: '100%',
  objectFit: 'cover',
  borderRadius: theme.spacing(1),
  border: theme.mixins.border('secondary'),
  height: '100%'
}));
interface Props {
  image: any;
  download_url?: string;
  images?: any;
  file_name?: any;
  isOwner?: boolean;
  keyIndex?: string;
}
export default function MsgAttachmentMultiImage({
  image,
  download_url,
  images,
  isOwner,
  file_name,
  keyIndex,
  id
}: Props) {
  const { dispatch, usePageParams, assetUrl } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const listImages = React.useMemo(() => {
    return images && images.length
      ? images.map((item: any, index: number) => {
          const itemSrc = getImageSrc(
            item.image,
            'origin',
            assetUrl('photo.no_image')
          );

          return {
            id: index,
            src: itemSrc,
            download_url: item.download_url,
            file_name: item.file_name,
            imageId: item.id
          };
        })
      : [];
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [images]);

  const imgSrc = getImageSrc(image, '500', assetUrl('photo.no_image'));
  const imgOriginSrc = getImageSrc(image, 'origin', assetUrl('photo.no_image'));

  const presentImageView = () => {
    dispatch({
      type: 'chat/room/presentImageView',
      payload: {
        image: {
          id: parseInt(keyIndex),
          src: imgOriginSrc,
          download_url,
          file_name,
          imageId: id
        },
        images: listImages
      }
    });
  };

  return (
    <UIMsgAttachMultiImgWrapper isPageAllMessages={isPageAllMessages}>
      <ImageStyled src={imgSrc} onClick={presentImageView} alt={'dwq'} />
    </UIMsgAttachMultiImgWrapper>
  );
}
