/**
 * @type: skeleton
 * name: poll.itemView.smallCard.skeleton
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
          <ImageSkeleton ratio="11" />
        </div>
        <div className={classes.inner}>
          <div className={classes.header}>
            <Skeleton width={'100%'} />
          </div>
          <Skeleton width={160} />
          <Skeleton width={160} />
        </div>
      </div>
    </ItemView>
  );
}
