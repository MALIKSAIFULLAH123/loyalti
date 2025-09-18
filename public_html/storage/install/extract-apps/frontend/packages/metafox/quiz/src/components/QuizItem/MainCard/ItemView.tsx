import { Link, useGlobal } from '@metafox/framework';
import { useBlock } from '@metafox/layout';
import { QuizItemProps } from '@metafox/quiz/types';
import {
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  PendingFlag,
  UserName
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import React from 'react';

const name = 'QuizItemMainCard';

const FlagWrapper = styled('span', {
  name,
  slot: 'FlagWrapper'
})(({ theme }) => ({
  display: 'flex'
}));

const ItemMinor = styled(Box, {
  name,
  slot: 'ItemMinor'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: 13,
  lineHeight: 1.2
}));

const ItemFlag = styled(Box, {
  name,
  slot: 'ItemMinor'
})(({ theme }) => ({
  position: 'absolute',
  right: -2,
  bottom: theme.spacing(1.5),
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'flex-end',
  [theme.breakpoints.down('sm')]: {
    bottom: 0
  }
}));

export default function QuizItemMainCard({
  item,
  itemProps,
  user,
  handleAction,
  state,
  identity,
  wrapAs,
  wrapProps
}: QuizItemProps) {
  const { ItemActionMenu, useIsMobile, i18n } = useGlobal();
  const isMobile = useIsMobile();

  const { itemLinkProps = {} } = useBlock();

  if (!item) return null;

  const { link: to } = item || {};

  const cover = getImageSrc(item.image, '240');

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <Link to={to} asModal={itemLinkProps.asModal} identityTracking={identity}>
        <ItemMedia src={cover} alt={item.title} backgroundImage />
      </Link>
      <ItemText>
        {isMobile ? (
          <FlagWrapper>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </FlagWrapper>
        ) : null}
        <ItemTitle>
          <Link
            to={to}
            color={'inherit'}
            children={item.title}
            identityTracking={identity}
          />
        </ItemTitle>
        {itemProps.showActionMenu ? (
          <ItemAction placement="top-end" spacing="normal">
            <ItemActionMenu
              identity={identity}
              icon={'ico-dottedmore-vertical-o'}
              state={state}
              handleAction={handleAction}
            />
          </ItemAction>
        ) : null}
        <ItemMinor>
          <UserName to={`/user/${user?.id}`} user={user} hoverCard={false} />
        </ItemMinor>
        <Typography variant="body2" color="text.secondary" mt={2}>
          {i18n.formatMessage(
            { id: 'total_play' },
            { value: item.statistic.total_play }
          )}
        </Typography>
        {!isMobile ? (
          <ItemFlag>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </ItemFlag>
        ) : null}
      </ItemText>
    </ItemView>
  );
}
