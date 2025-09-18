import { EventItemProps } from '@metafox/event/types';
import { Link, useGlobal } from '@metafox/framework';
import {
  FormatDate,
  Image,
  ItemMedia,
  ItemView,
  TruncateText
} from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import clsx from 'clsx';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import useStyles from './styles';
import { getTopPosition } from '@metafox/event/utils';
import { styled } from '@mui/material/styles';
import { isEmpty } from 'lodash';

const ItemMediaWrapper = styled(ItemMedia, {
  name: 'ItemMediaWrapper',
  shouldForwardProp: props => props !== 'position' && props !== 'isCover'
})<{ position: number; isCover?: boolean }>(({ theme, position, isCover }) => ({
  '& img': {
    border: 'none',
    height: '100%',
    ...(isCover && {
      objectPosition: 'top'
    }),
    ...(position && {
      objectPosition: 'top',
      transform: `translateY(${position})`,
      height: 'auto !important',
      minHeight: '100%'
    })
  }
}));

export default function EventUpcomingCardItem({
  item,
  identity,
  wrapProps,
  wrapAs
}: EventItemProps) {
  const classes = useStyles();
  const { i18n, assetUrl } = useGlobal();

  if (!item) return null;

  const { link: to } = item;

  const cover = getImageSrc(
    item.image,
    '500',
    assetUrl('event.cover_no_image')
  );

  const isCover = !isEmpty(item?.image);

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={clsx(classes.root, classes.upcomingItem)}>
        <div className={classes.outer}>
          {cover ? (
            <ItemMediaWrapper
              isCover={isCover}
              position={getTopPosition(item?.image_position)}
            >
              <Link to={to}>
                <Image src={cover} aspectRatio={'165'} />
              </Link>
            </ItemMediaWrapper>
          ) : null}
          <div className={classes.inner}>
            <Link to={to} className={classes.title}>
              <TruncateText variant={'subtitle2'} lines={1} fixHeight>
                {item.title}
              </TruncateText>
            </Link>
            <div className={clsx(classes.itemMinor, classes.startDate)}>
              <FormatDate
                data-testid="startTime"
                value={item.start_time}
                format="LL"
              />{' '}
              {item.location?.address && i18n.formatMessage({ id: 'at' })}{' '}
              {item.location?.address}
            </div>
          </div>
        </div>
      </div>
    </ItemView>
  );
}

EventUpcomingCardItem.LoadingSkeleton = LoadingSkeleton;
