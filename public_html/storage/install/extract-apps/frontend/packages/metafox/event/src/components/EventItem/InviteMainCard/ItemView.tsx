import { APP_EVENT, STATUS_ONGOING } from '@metafox/event';
import { EventItemProps } from '@metafox/event/types';
import { isEventEnd } from '@metafox/event/utils';
import { Link, useGlobal } from '@metafox/framework';
import {
  FeaturedFlag,
  FormatDate,
  Image,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  SponsorFlag
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, IconButton, styled, Typography, Chip } from '@mui/material';
import React from 'react';
import { isEqual } from 'lodash';
import InterestedButton from '../../InterestedButton';
import LoadingSkeleton from './LoadingSkeleton';

const name = 'EventInviteItemMainCard';

const ItemStyled = styled(ItemView, { name, slot: 'itemStyled' })(() => ({
  height: 350
}));

const ItemTitleStyled = styled(ItemTitle, { name, slot: 'itemTitleStyled' })(
  ({ theme }) => ({
    minHeight: 48,
    marginBottom: theme.spacing(1)
  })
);

const TimeStyled = styled(Box, { name, slot: 'timeStyled' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  div: {
    marginRight: theme.spacing(1)
  }
}));

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

export default function EventInviteItemMainCard({
  item,
  user,
  identity,
  itemProps,
  handleAction,
  state,
  wrapProps,
  wrapAs
}: EventItemProps) {
  const { ItemActionMenu, assetUrl, i18n, useResourceMenu } = useGlobal();
  const menus = useResourceMenu(APP_EVENT, APP_EVENT, 'interestedMenu');

  const compactMenu = menus.items.map(item => ({
    ...item,
    params: { keepEntity: false }
  }));

  if (!item) return null;

  const {
    id,
    image,
    title,
    location,
    rsvp,
    is_featured,
    is_sponsor,
    is_online,
    status
  } = item;

  const to = `/event/${id}`;

  const cover = getImageSrc(image, '500', assetUrl('event.cover_no_image'));

  const isEnd = isEventEnd(item.end_time);

  return (
    <ItemStyled
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
    >
      <ItemMedia>
        <Image link={to} src={cover} aspectRatio={'32'} />
      </ItemMedia>
      <FlagWrapper>
        <FeaturedFlag variant="itemView" value={is_featured} />
        <SponsorFlag variant="itemView" value={is_sponsor} item={item} />
      </FlagWrapper>
      <ItemText>
        <ItemTitleStyled>
          <Link color={'inherit'} to={to} children={title} />
        </ItemTitleStyled>
        <TimeStyled>
          {status === STATUS_ONGOING ? (
            <Chip
              size="small"
              label="Ongoing"
              variant="filled"
              sx={{
                color: 'default.contrastText',
                backgroundColor: 'success.main',
                fontSize: '13px'
              }}
            />
          ) : null}
          <Typography
            component="div"
            variant="body2"
            textTransform="uppercase"
            color="primary"
          >
            <FormatDate
              data-testid="startedDate"
              value={item.start_time}
              format="llll"
            />
          </Typography>
        </TimeStyled>
        {is_online ? (
          <ItemSummary>{i18n.formatMessage({ id: 'online' })}</ItemSummary>
        ) : (
          <ItemSummary>{location?.address}</ItemSummary>
        )}
        <Box display="flex" mt={0.5}>
          <Box sx={{ flex: 1, minWidth: 0, display: 'flex' }}>
            <InterestedButton
              disabled={!!isEnd || isEqual(item.extra?.can_rsvp, false)}
              handleAction={handleAction}
              identity={identity}
              rsvp={rsvp}
              menus={compactMenu}
            />
          </Box>
          {itemProps.showActionMenu ? (
            <ItemActionMenu
              identity={identity}
              state={state}
              handleAction={handleAction}
              sx={{ ml: 1 }}
              control={
                <IconButton
                  color="primary"
                  variant="outlined-square"
                  size="medium"
                >
                  <LineIcon icon={'ico-dottedmore-o'} />
                </IconButton>
              }
            />
          ) : null}
        </Box>
      </ItemText>
    </ItemStyled>
  );
}

EventInviteItemMainCard.LoadingSkeleton = LoadingSkeleton;
