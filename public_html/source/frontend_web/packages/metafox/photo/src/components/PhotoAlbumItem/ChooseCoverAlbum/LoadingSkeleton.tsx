/**
 * @type: skeleton
 * name: photo_album.itemView.chooseCoverPhoto.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <div className={classes.outer}>
        <div className={classes.media}>
          <Skeleton className={classes.media} variant="rectangular" />
        </div>
      </div>
      <div className={classes.inner}>
        <Skeleton variant="text" />
        <Skeleton variant="text" />
        <Skeleton variant="text" />
      </div>
    </ItemView>
  );
}
