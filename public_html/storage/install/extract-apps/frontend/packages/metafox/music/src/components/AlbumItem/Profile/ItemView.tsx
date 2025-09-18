import { Link, useGlobal } from '@metafox/framework';
import { AlbumItemProps } from '@metafox/music/types';
import {
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  SponsorFlag,
  Image
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { Box, styled } from '@mui/material';

const ImageMedia = styled(Image, {
  name: 'ImageMedia'
})(({ theme }) => ({
  display: 'flex',
  '& img': {
    border: 'none'
  }
}));

const OverlayStyled = styled(Box, {
  shouldForwardProp: props => props !== 'isMobile'
})(({ theme }) => ({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  backgroundColor: 'rgba(0,0,0,0.4)',
  opacity: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',

  '& .iconPlay': {
    color: '#fff',
    fontSize: 48,
    position: 'relative'
  },
  '&:hover': {
    opacity: 1
  }
}));

const ItemFlag = styled(Box, {
  name: 'ItemFlag'
})(({ theme }) => ({
  position: 'absolute',
  right: -2,
  bottom: theme.spacing(1.5),
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end'
}));

const ItemMinor = styled(Box, {
  name: 'ItemMinor'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: 13,
  lineHeight: 1.2,
  marginBottom: theme.spacing(1.2),
  paddingRight: theme.spacing(2),
  overflow: 'hidden',
  maxWidth: '100%',
  whiteSpace: 'nowrap',
  textOverflow: 'ellipsis',
  '& a': {
    color: theme.palette.text.secondary
  }
}));

function AlbumMainCardItem({
  item,
  identity,
  wrapAs,
  wrapProps,
  user,
  itemProps
}: AlbumItemProps) {
  const { useActionControl, ItemActionMenu, assetUrl } = useGlobal();
  const [control, state] = useActionControl(identity, {
    menuOpened: false
  });

  if (!item) return null;

  const { link, name } = item;

  const to = link || `/music/album/${item.id}`;

  const cover = getImageSrc(
    item.image,
    '240',
    assetUrl('music.album_no_image')
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <ItemMedia src={cover} backgroundImage>
        <Link to={to} identityTracking={identity}>
          <Box sx={{ position: 'relative' }}>
            <ImageMedia src={cover} aspectRatio={'11'} />
            <OverlayStyled>
              <LineIcon className="iconPlay" icon="ico-play-circle-o" />
            </OverlayStyled>
          </Box>
        </Link>
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Link
            to={to}
            children={name}
            color={'inherit'}
            identityTracking={identity}
          />
        </ItemTitle>
        <ItemAction placement="bottom-end" sx={{ pr: 1, pb: 1 }}>
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            handleAction={control}
          />
        </ItemAction>
        <ItemMinor>{item.year}</ItemMinor>
      </ItemText>
      <ItemFlag>
        <FeaturedFlag variant="itemView" value={item.is_featured} />
        <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
      </ItemFlag>
    </ItemView>
  );
}

AlbumMainCardItem.LoadingSkeleton = LoadingSkeleton;

export default AlbumMainCardItem;
