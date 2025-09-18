import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentImage';

const UIMsgAttachmentImgWrapper = styled('div', { name })(({ theme }) => ({
  position: 'relative',
  width: '100%'
}));

const MediaWrapper = styled('figure', { name })(({ theme }) => ({
  margin: 0,
  display: 'block'
}));
const ImgRatioWrapper = styled('div', {
  name,
  shouldForwardProp: props =>
    props !== 'isOwner' && props !== 'isPageAllMessages'
})<{ isOwner?: boolean; isPageAllMessages?: boolean }>(
  ({ theme, isOwner, isPageAllMessages }) => ({
    width: '100%',
    minWidth: isOwner ? '200px' : '180px',
    maxWidth: '100%',
    cursor: 'pointer',
    ...(isPageAllMessages && {
      width: '300px',
      [theme.breakpoints.down('sm')]: {
        width: isOwner ? '200px' : '180px'
      }
    })
  })
);
const ImageStyled = styled('img', { name })(({ theme }) => ({
  position: 'absolute',
  left: 0,
  top: 0,
  width: '100%',
  borderRadius: theme.spacing(1),
  border: theme.mixins.border('secondary')
}));
interface Props {
  image_url: string;
  title: string;
  image_dimensions?: any;
  keyIndex?: string;
  isOwner?: boolean;
  allowOpenPreview?: boolean;
}
export default function MsgAttachmentImage({
  isOwner,
  title,
  image_url,
  image_dimensions,
  allowOpenPreview = true
}: Props) {
  const { chatplus, usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const ratioImage = image_dimensions
    ? image_dimensions.height / image_dimensions.width
    : false;

  const handleViewImage = React.useCallback(
    (e: any) => {
      if (!allowOpenPreview) return;

      chatplus.presentImageView(e, {
        id: 'img0',
        src: chatplus.sanitizeRemoteFileUrl(image_url)
      });
    },
    [chatplus, image_url, allowOpenPreview]
  );

  return (
    <UIMsgAttachmentImgWrapper>
      <MediaWrapper>
        <ImgRatioWrapper
          isOwner={!!isOwner}
          isPageAllMessages={isPageAllMessages}
          style={{ paddingBottom: `${ratioImage ? ratioImage * 100 : 100}%` }}
        >
          <ImageStyled
            src={`${chatplus.sanitizeRemoteFileUrl(image_url)}&width=300`}
            onClick={handleViewImage}
            alt={title}
          />
        </ImgRatioWrapper>
      </MediaWrapper>
    </UIMsgAttachmentImgWrapper>
  );
}
