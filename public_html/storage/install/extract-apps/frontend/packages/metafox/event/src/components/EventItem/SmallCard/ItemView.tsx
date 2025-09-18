import { Link, useGlobal } from '@metafox/framework';
import { EventItemProps } from '@metafox/event/types';
import {
  FeaturedFlag,
  FormatDate,
  Image,
  ItemView,
  LineIcon,
  SponsorFlag,
  TruncateText
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { IconButton } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import useStyles from './styles';

export default function EventSmallCardItem({
  item,
  identity,
  handleAction,
  state,
  wrapProps,
  wrapAs
}: EventItemProps) {
  const { i18n, ItemActionMenu, assetUrl } = useGlobal();
  const classes = useStyles();

  if (!item) return null;

  const { link: to } = item;

  const cover = getImageSrc(
    item.image,
    '500',
    assetUrl('event.cover_no_image')
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={classes.root}>
        <div className={classes.outer}>
          <div className={classes.itemFlag}>
            <FeaturedFlag variant="itemView" value={item.is_featured} />
            <SponsorFlag
              variant="itemView"
              value={item.is_sponsor}
              item={item}
            />
          </div>
          {cover ? (
            <Link to={to} className={classes.media}>
              <Image
                link={to}
                src={cover}
                aspectRatio={'169'}
                backgroundImage
              />
            </Link>
          ) : null}
          <div className={classes.inner}>
            <Link to={to} className={classes.title}>
              <TruncateText variant="h4" lines={2} fixHeight>
                {item.title}
              </TruncateText>
            </Link>
            <div className={clsx(classes.itemMinor, classes.startDate)}>
              <FormatDate
                data-testid="startTime"
                value={item.start_time}
                format="'h:mm a - DD MMMM, yyyy'"
              />
            </div>
            <div className={clsx(classes.itemMinor, classes.location)}>
              {item.location}
            </div>
            <div className={classes.eventActions}>
              <IconButton
                size="small"
                color="primary"
                className={classes.iconButton}
              >
                <LineIcon icon={'ico-calendar-star-o'} />
                {i18n.formatMessage({ id: 'interested' })}
              </IconButton>
              <ItemActionMenu
                identity={identity}
                state={state}
                handleAction={handleAction}
                className={classes.actionMenu}
              >
                <LineIcon icon={'ico-dottedmore-o'} />
              </ItemActionMenu>
            </div>
          </div>
        </div>
      </div>
    </ItemView>
  );
}

EventSmallCardItem.LoadingSkeleton = LoadingSkeleton;
