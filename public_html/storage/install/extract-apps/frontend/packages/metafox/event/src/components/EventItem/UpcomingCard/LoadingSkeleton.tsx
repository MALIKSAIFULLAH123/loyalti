/**
 * @type: skeleton
 * name: event.itemView.upcomingCard.skeleton
 */

import { ImageSkeleton, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <div className={classes.outer}>
        <div className={classes.media}>
          <ImageSkeleton ratio="165" />
        </div>
        <div className={classes.inner}>
          <Skeleton width={'100%'} />
          <Skeleton width={'100%'} />
          <Skeleton width={160} />
          <Skeleton width={160} />
        </div>
      </div>
    </ItemView>
  );
}
