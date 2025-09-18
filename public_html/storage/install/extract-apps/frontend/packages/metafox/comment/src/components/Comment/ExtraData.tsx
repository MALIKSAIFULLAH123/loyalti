import { useGlobal } from '@metafox/framework';
import { Image } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';
import { CommentExtraDataProps } from '../../types';
import { get } from 'lodash';

const name = 'CommentExtraData';

const ExtraDataPhoto = styled('div', { name, slot: 'extraDataPhoto' })(
  ({ theme }) => ({
    maxWidth: theme.spacing(26),
    cursor: 'pointer'
  })
);
const ExtraDataSticker = styled('div', {
  name,
  slot: 'ExtraDataSticker'
})(({ theme }) => ({
  width: theme.spacing(10),
  height: theme.spacing(10)
}));

const ExtraDataGif = styled('div', {
  name,
  slot: 'ExtraDataGif'
})(({ theme }) => ({
  width: theme.spacing(25),
  height: 'auto'
}));
const BubbleExtraData = styled('div', { name, slot: 'bubbleExtraData' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1)
  })
);

export default function CommentExtraData(props: CommentExtraDataProps) {
  const { extra_data } = props;
  const { extra_type, image, params } = Object.assign({}, extra_data);
  const { dispatch, jsxBackend } = useGlobal();
  const EmbedItem = jsxBackend.get('feedArticle.comment.embedItem');

  if (!extra_data) return null;

  const src = getImageSrc(image, '500');

  const presentPhoto = src => {
    dispatch({
      type: 'photo/presentSimplePhoto',
      payload: { src, alt: 'photo', identity: extra_data?._identity }
    });
  };

  let content = null;
  switch (extra_type) {
    case 'photo':
    case 'storage_file':
      content = (
        <ExtraDataPhoto onClick={() => presentPhoto(src)}>
          <Image src={src} alt={'photo'} shape={'radius'} />
        </ExtraDataPhoto>
      );
      break;
    case 'sticker':
      content = (
        <ExtraDataSticker>
          <Image
            src={src}
            alt={'sticker'}
            aspectRatio={'fixed'}
            imageFit={'contain'}
          />
        </ExtraDataSticker>
      );
      break;
    case 'link':
      if (params?.is_hidden) {
        content = null;
      } else {
        content = params?.is_image ? (
          <ExtraDataPhoto onClick={() => presentPhoto(params?.image)}>
            <Image src={params?.image} alt={'photo'} shape={'radius'} />
          </ExtraDataPhoto>
        ) : (
          <EmbedItem {...params} widthImage="90px" />
        );
      }

      break;
    case 'gif': {
      content = (
        <ExtraDataGif>
          <Image
            src={get(params?.images, 'fixed_width.url')}
            alt={'gif'}
            aspectRatio={'auto'}
            imageFit={'contain'}
          />
        </ExtraDataGif>
      );
      break;
    }
  }

  if (!content) return null;

  return <BubbleExtraData>{content}</BubbleExtraData>;
}
