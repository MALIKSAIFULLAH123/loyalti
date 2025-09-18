import { Link, useGlobal } from '@metafox/framework';
import { PlaylistItemProps } from '@metafox/music/types';
import {
  FeaturedFlag,
  Image,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  SponsorFlag,
  Statistic,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

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
  bottom: theme.spacing(1.5),
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end'
}));

function PlaylistItemMainCard({
  item,
  identity,
  wrapAs,
  wrapProps,
  user
}: PlaylistItemProps) {
  const { useActionControl, ItemActionMenu, assetUrl } = useGlobal();
  const [control, state] = useActionControl<unknown, unknown>(identity, {
    menuOpened: false
  });

  if (!item) return null;

  const { link: to } = item;

  const cover = getImageSrc(
    item.image,
    '240',
    assetUrl('music.playlist_no_image')
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia src={cover} backgroundImage>
        <Link to={to}>
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
          <Link to={to} children={item.name} color={'inherit'} />
        </ItemTitle>
        <ItemAction placement="top-end" spacing="normal">
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            handleAction={control}
          />
        </ItemAction>
        <Box mb={1}>
          <Statistic
            values={item.statistic}
            display="total_song"
            fontStyle="minor"
            skipZero={false}
          />
        </Box>
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

PlaylistItemMainCard.LoadingSkeleton = LoadingSkeleton;

export default PlaylistItemMainCard;
