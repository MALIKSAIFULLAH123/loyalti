/**
 * @type: skeleton
 * name: poll.itemView.profileCard.skeleton
 */
import { useGlobal } from '@metafox/framework';
import {
  ImageSkeleton,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();
  const { useIsMobile } = useGlobal();
  const isMobile = useIsMobile();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <ImageSkeleton ratio={isMobile ? '169' : '11'} borderRadius={0} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton width={'100%'} />
        </ItemTitle>
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
