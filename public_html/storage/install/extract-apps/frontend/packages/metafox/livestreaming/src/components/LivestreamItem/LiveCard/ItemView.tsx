import { Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import {
  FeaturedFlag,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  PendingFlag,
  SponsorFlag,
  ItemAction,
  UserAvatar,
  ItemUserShape,
  FromNow,
  PrivacyIcon,
  DotSeparator
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { LivestreamItemProps } from '@metafox/livestreaming/types';
import { Box, styled } from '@mui/material';
import * as React from 'react';

const ItemFlag = styled(Box)(({ theme }) => ({
  position: 'absolute',
  right: 0,
  top: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  zIndex: 5
}));

const LivestreamItemMainCard = ({
  item,
  user,
  identity,
  itemProps,
  handleAction,
  state,
  wrapAs,
  wrapProps
}: LivestreamItemProps) => {
  const { ItemActionMenu, jsxBackend } = useGlobal();
  const { itemLinkProps } = useBlock();
  const MediaLayer = jsxBackend.get('livestreaming.ui.overlayVideo');

  if (!item) return null;

  const { link: to, thumbnail_url } = item;

  const cover = getImageSrc(thumbnail_url, '240', '');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemFlag>
        <FeaturedFlag variant="itemView" value={item.is_featured} />
        <SponsorFlag variant="itemView" value={item.is_sponsor} item={item} />
        <PendingFlag variant="itemView" value={item.is_pending} />
      </ItemFlag>
      <ItemMedia src={cover} backgroundImage>
        <MediaLayer item={item} />
      </ItemMedia>
      <ItemText>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Box mr={1.5}>
            <UserAvatar user={user as ItemUserShape} size={48} />
          </Box>
          <Box>
            <Link
              variant="body1"
              color={'text.primary'}
              to={user.link}
              children={user?.full_name}
              hoverCard={`/user/${user.id}`}
              sx={{ fontWeight: 'bold' }}
            />
            <Box mt={0.5}>
              <DotSeparator sx={{ color: 'text.secondary' }}>
                <FromNow value={item?.creation_date} />
                <PrivacyIcon item={item.privacy_detail} value={item.privacy} />
              </DotSeparator>
            </Box>
          </Box>
        </Box>
        {item.title ? (
          <ItemTitle>
            <Link
              to={to}
              sx={{ fontWeight: 'bold' }}
              asModal
              {...itemLinkProps}
            >
              {item.title}
            </Link>
          </ItemTitle>
        ) : null}
        {itemProps.showActionMenu ? (
          <ItemAction placement="bottom-end" sx={{ bottom: '8px', right: 0 }}>
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
      </ItemText>
    </ItemView>
  );
};

export default LivestreamItemMainCard;
