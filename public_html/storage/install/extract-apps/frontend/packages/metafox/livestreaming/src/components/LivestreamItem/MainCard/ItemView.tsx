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
  Statistic,
  ItemAction,
  ItemSubInfo,
  FormatDateRelativeToday,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { LivestreamItemProps } from '@metafox/livestreaming/types';
import { Box, Typography, styled } from '@mui/material';
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

  const { link: to, creation_date, is_streaming, thumbnail_url, title } = item;

  const cover = getImageSrc(thumbnail_url, '500', '');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
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
        <ItemTitle>
          <Link to={to} asModal identityTracking={identity} {...itemLinkProps}>
            {title}
          </Link>
        </ItemTitle>
        <ItemSubInfo>
          <UserName user={user} data-testid="itemAuthor" hoverCard={false} />
        </ItemSubInfo>
        <Typography color="text.hint">
          {is_streaming ? (
            <FormatDateRelativeToday value={creation_date} />
          ) : (
            <Statistic
              color="text.hint"
              values={item.statistic}
              display={'total_view'}
            />
          )}
        </Typography>
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
