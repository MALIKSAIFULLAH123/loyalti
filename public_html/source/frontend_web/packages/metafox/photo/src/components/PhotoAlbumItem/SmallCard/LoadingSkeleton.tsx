/**
 * @type: skeleton
 * name: photo_album.itemView.smallCard.skeleton
 */
import { ImageSkeleton, ItemView } from '@metafox/ui';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <div className={classes.outer}>
        <ImageSkeleton ratio="11" />
      </div>
    </ItemView>
  );
}
