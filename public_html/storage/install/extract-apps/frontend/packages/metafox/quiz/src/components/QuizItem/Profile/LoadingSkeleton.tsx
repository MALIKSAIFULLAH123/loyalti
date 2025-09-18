/**
 * @type: skeleton
 * name: quiz.itemView.profileCard.skeleton
 */
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio="11" borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <div className={classes.itemMinor}>
          <Skeleton width={160} />
        </div>
        <div className={classes.itemStatistic}>
          <Skeleton width={160} />
        </div>
      </ItemText>
    </ItemView>
  );
}
