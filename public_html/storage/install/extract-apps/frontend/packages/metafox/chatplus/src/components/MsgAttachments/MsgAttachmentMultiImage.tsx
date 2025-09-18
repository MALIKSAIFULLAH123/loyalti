import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const name = 'MsgAttachmentImage';

const UIMsgAttachmentImgWrapper = styled('div', {
  name,
  slot: 'UIMsgAttachmentImgWrapper'
})(({ theme }) => ({
  position: 'relative',
  width: '100%'
}));

const MediaWrapper = styled('figure', { name })(({ theme }) => ({
  margin: 0,
  display: 'block'
}));
const ImgRatioWrapper = styled('div', {
  name,
  shouldForwardProp: props => props !== 'isPageAllMessages'
})<{
  isPageAllMessages?: boolean;
}>(({ theme, isPageAllMessages }) => ({
  maxWidth: '100%',
  cursor: 'pointer',
  marginBottom: '1px',
  width: '100%',
  height: '100%',

  // 2. style page all
  ...(isPageAllMessages && {
    width: '100%',
    height: '100%'
  })
}));
const ImageStyled = styled('img', {
  name
})<{}>(({ theme }) => ({
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
  image_url: string;
  title: string;
  images?: any;
  keyIndex?: string;
  isOwner?: boolean;
  typeGridLayout?: string;
  msgType?: 'message_pinned' | 'message_unpinned' | string;
  isOther?: boolean;
}
export default function MsgAttachmentMultiImage({
  image_url,
  images,
  keyIndex
}: Props) {
  const { chatplus, usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const isPageAllMessages = pageParams?.rid || false;

  const listImages =
    images && images.length
      ? images.map((item, index) => {
          return {
            id: index,
            src: chatplus.sanitizeRemoteFileUrl(item.image_url)
          };
        })
      : [];

  return (
    <UIMsgAttachmentImgWrapper>
      <MediaWrapper>
        <ImgRatioWrapper
          isPageAllMessages={isPageAllMessages}
          style={{ paddingBottom: '100%' }}
        >
          <ImageStyled
            src={`${chatplus.sanitizeRemoteFileUrl(image_url)}&width=150`}
            onClick={e => {
              chatplus.presentImageView(
                e,
                {
                  id: parseInt(keyIndex),
                  src: chatplus.sanitizeRemoteFileUrl(image_url)
                },
                listImages
              );
            }}
            alt={'MsgAttachmentMultiImage'}
          />
        </ImgRatioWrapper>
      </MediaWrapper>
    </UIMsgAttachmentImgWrapper>
  );
}
