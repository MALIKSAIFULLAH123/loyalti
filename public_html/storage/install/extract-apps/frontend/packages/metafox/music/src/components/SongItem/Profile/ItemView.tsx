import { Link, useGetItem, useGlobal } from '@metafox/framework';
import { SongItemProps } from '@metafox/music/types';
import {
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  Image,
  LineIcon,
  PendingFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const ItemMediaWrapper = styled(ItemMedia, {
  name: 'ItemMediaWrapper'
})(({ theme }) => ({
  position: 'relative'
}));

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

function SongItemMainCard({
  item,
  identity,
  wrapAs,
  wrapProps,
  user,
  handleAction,
  itemProps
}: SongItemProps) {
  const { useActionControl, ItemActionMenu, assetUrl } = useGlobal();
  const [state] = useActionControl<unknown, unknown>(identity, {
    menuOpened: false
  });

  const { i18n } = useGlobal();
  const album = useGetItem(item?.album);

  if (!item) return null;

  const { link, name } = item;

  const cover = getImageSrc(item.image, '240', assetUrl('music.song_no_image'));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <ItemMediaWrapper>
        <Link to={link} identityTracking={identity}>
          <Box sx={{ position: 'relative' }}>
            <ImageMedia src={cover} aspectRatio={'11'} />
            <OverlayStyled>
              <LineIcon className="iconPlay" icon="ico-play-circle-o" />
            </OverlayStyled>
          </Box>
        </Link>
      </ItemMediaWrapper>
      <ItemText>
        <ItemTitle>
          <Link
            to={link}
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
            handleAction={handleAction}
          />
        </ItemAction>
        {album && (
          <ItemAuthor>
            <span>{`${i18n.formatMessage({ id: 'album' })}: `}</span>
            <Link
              to={album?.link}
              children={album?.name}
              color="inherit"
              sx={{ fontWeight: 'bold' }}
            />
          </ItemAuthor>
        )}
      </ItemText>
      <ItemFlag>
        <FeaturedFlag variant="itemView" value={item.is_featured} />
        <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
        <PendingFlag variant="itemView" value={item.is_pending} />
      </ItemFlag>
    </ItemView>
  );
}

SongItemMainCard.LoadingSkeleton = LoadingSkeleton;

export default SongItemMainCard;
