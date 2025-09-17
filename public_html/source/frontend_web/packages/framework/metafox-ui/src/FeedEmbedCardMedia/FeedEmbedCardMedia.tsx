import { FeedEmbedCardMediaProps, Image } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { styled, Box } from '@mui/material';
import React from 'react';

const Root = styled(Box, {
  name: 'MuiFeedEmbedCardMedia',
  slot: 'bgCover',
  shouldForwardProp: prop => prop !== 'widthImage' && prop !== 'heightImage'
})<{ widthImage?: string; heightImage?: string }>(
  ({ theme, widthImage, heightImage }) => ({
    width: widthImage,
    height: heightImage,
    '& img': {
      border: '1px solid transparent',
      borderRightColor: theme.palette.border?.secondary,
      borderTop: 'none',
      borderBottom: 'none',
      borderLeft: 'none'
    }
  })
);

export default function FeedEmbedCardMedia({
  image,
  widthImage = '200px',
  heightImage = 'auto',
  mediaRatio = '11',
  link,
  playerOverlay = false,
  playerOverlayProps = {},
  host,
  sx
}: FeedEmbedCardMediaProps) {
  return (
    <Root
      widthImage={widthImage}
      heightImage={heightImage}
      sx={sx}
      className={'media'}
    >
      <Image
        link={link}
        src={getImageSrc(image)}
        aspectRatio={mediaRatio}
        playerOverlay={playerOverlay}
        playerOverlayProps={playerOverlayProps}
      />
    </Root>
  );
}
