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
  Image,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { Box, styled } from '@mui/material';

const ItemMediaWrapper = styled(ItemMedia, {
  name: 'ItemMediaWrapper'
})(({ theme }) => ({
  marginBottom: theme.spacing(2)
}));

const ImageMedia = styled(Image, {
  name: 'ImageMedia'
})(({ theme }) => ({
  display: 'flex',
  '& img': {
    border: 'none'
  }
}));

const ItemActionWrapper = styled(ItemAction, {
  name: 'ItemActionWrapper'
})(({ theme }) => ({
  padding: theme.spacing(0, 0, 0, 2),
  top: 'auto',
  bottom: theme.spacing(-0.75),
  right: theme.spacing(0)
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
  opacity: 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer',
  '& .iconPlay': {
    color: '#fff',
    fontSize: 48,
    position: 'relative'
  }
}));

const ItemAuthor = styled(Box, {
  name: 'ItemAuthor'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: 13,
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

const ItemFlag = styled(Box, {
  name: 'ItemFlag'
})(({ theme }) => ({
  position: 'absolute',
  right: -2,
  bottom: 'auto',
  top: theme.spacing(1.5),
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end'
}));

const Title = styled(ItemTitle, {
  name: 'Title'
})(({ theme }) => ({
  marginBottom: `${theme.spacing(1)} !important`,
  marginRight: `${theme.spacing(0)} !important`
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

function AlbumItemSmallCard({
  item,
  identity,
  wrapAs,
  wrapProps,
  user
}: AlbumItemProps) {
  const { useActionControl, ItemActionMenu, assetUrl } = useGlobal();
  const [control, state] = useActionControl(identity, {
    menuOpened: false
  });

  if (!item) return null;

  const { link } = item;

  const to = link || `/music/album/${item.id}`;

  const cover = getImageSrc(
    item.image,
    '500',
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
      <ItemMediaWrapper src={cover} backgroundImage>
        <Link to={to} identityTracking={identity}>
          <Box sx={{ position: 'relative' }}>
            <ImageMedia src={cover} aspectRatio={'169'} />
            <OverlayStyled>
              <LineIcon className="iconPlay" icon="ico-play-circle-o" />
            </OverlayStyled>
          </Box>
        </Link>
      </ItemMediaWrapper>
      <ItemText>
        <Title>
          <Link
            to={to}
            children={item.name}
            color={'inherit'}
            identityTracking={identity}
          />
        </Title>
        <ItemActionWrapper placement="top-end" spacing="normal">
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            handleAction={control}
          />
        </ItemActionWrapper>
        <ItemMinor>{item.year}</ItemMinor>
        <ItemAuthor>
          <UserName color="inherit" to={user.link} user={user} />
        </ItemAuthor>
      </ItemText>
      <ItemFlag>
        <FeaturedFlag variant="itemView" value={item.is_featured} />
        <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
      </ItemFlag>
    </ItemView>
  );
}

AlbumItemSmallCard.LoadingSkeleton = LoadingSkeleton;

export default AlbumItemSmallCard;
