import { Link, useGlobal } from '@metafox/framework';
import { usePageParams } from '@metafox/layout';
import { AlbumItemProps } from '@metafox/photo/types';
import {
  FeaturedFlag,
  Image,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  Statistic,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import React from 'react';

const name = 'PhotoAlbumItemMainCard';

const ItemViewStyled = styled(ItemView, { name, slot: 'root' })(
  ({ theme }) => ({
    transition: 'all 0.2s ease',
    position: 'relative',
    '& .actionMenu': {
      position: 'absolute !important',
      right: theme.spacing(1),
      bottom: theme.spacing(-1),
      opacity: 1
    }
  })
);

const FlagWrapper = styled('div', { name, slot: 'flagWrapper' })(
  ({ theme }) => ({
    position: 'absolute',
    top: theme.spacing(2),
    right: theme.spacing(-0.25),
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'flex-end'
  })
);

const PhotoAlbumItemMainCard = ({
  item,
  user,
  identity,
  itemProps,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: AlbumItemProps) => {
  const { ItemActionMenu, assetUrl } = useGlobal();
  const pageParams = usePageParams();
  const { module_name, profile_page, profile_id } = pageParams;

  if (!item) return null;

  const { name, statistic, is_featured, is_sponsor, link } = item;
  let to = link;

  if (module_name === 'user' && profile_page && profile_id) {
    to = `/user/${profile_id}/photo?stab=albums&album_id=${item.id}`;
  }

  const cover = getImageSrc(
    item.image,
    '500',
    assetUrl('photo.album_no_image')
  );

  return (
    <ItemViewStyled
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <ItemMedia>
        <Link to={to} identityTracking={identity}>
          <Image src={cover} aspectRatio={'32'} />
        </Link>
      </ItemMedia>
      <FlagWrapper>
        <FeaturedFlag variant="itemView" value={is_featured} />
        <SponsorFlag value={is_sponsor} variant="itemView" item={item} />
      </FlagWrapper>
      <ItemText>
        <ItemTitle>
          <Link
            color={'inherit'}
            to={to}
            children={name}
            identityTracking={identity}
          />
        </ItemTitle>
        <Box>
          <ItemSummary>
            <UserName color={'inherit'} to={`/user/${user?.id}`} user={user} />
          </ItemSummary>
          <Statistic values={statistic} display="total_item" skipZero={false} />
          {itemProps.showActionMenu ? (
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
              className={'actionMenu'}
            />
          ) : null}
        </Box>
      </ItemText>
    </ItemViewStyled>
  );
};

export default PhotoAlbumItemMainCard;
