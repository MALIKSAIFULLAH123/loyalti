/**
 * @type: skeleton
 * name: video.itemView.smallCard.skeleton
 */
import { ImageSkeleton, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <div className={classes.outer}>
        <div className={classes.media}>
          <ImageSkeleton ratio="169" />
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
