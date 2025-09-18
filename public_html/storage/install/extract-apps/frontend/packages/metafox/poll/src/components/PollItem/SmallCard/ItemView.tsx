import { Link, useGlobal } from '@metafox/framework';
import { PollItemProps } from '@metafox/poll/types';
import { Image, ItemView, Statistic, TruncateText } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import useStyles from './styles';

export default function PollItemSmallCard({
  item,
  user,
  handleAction,
  state,
  identity,
  wrapAs,
  wrapProps
}: PollItemProps) {
  const classes = useStyles();
  const { ItemActionMenu, assetUrl } = useGlobal();

  if (!item) return null;

  const to = `/poll/${item.id}`;
  const cover = getImageSrc(item.image, '240', assetUrl('poll.cover_no_image'));

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <div className={`${classes.root} ${classes.smallView}`}>
        <div className={classes.outer}>
          {cover ? (
            <div className={classes.media}>
              <Image link={to} src={cover} aspectRatio={'11'} backgroundImage />
            </div>
          ) : null}
          <div className={classes.inner}>
            <div className={classes.header}>
              <Link to={to} className={classes.title}>
                <TruncateText variant={'body1'} lines={2}>
                  {item.question}
                </TruncateText>
              </Link>
              <ItemActionMenu
                identity={identity}
                icon={'ico-dottedmore-vertical-o'}
                state={state}
                handleAction={handleAction}
              />
            </div>
            <div className={classes.itemMinor}>
              <Link to={`/user/${user?.id}`} children={user?.full_name} />
            </div>
            <Statistic
              className={classes.itemMinor}
              values={item.statistic}
              display={'total_votes'}
            />
          </div>
        </div>
      </div>
    </ItemView>
  );
}
