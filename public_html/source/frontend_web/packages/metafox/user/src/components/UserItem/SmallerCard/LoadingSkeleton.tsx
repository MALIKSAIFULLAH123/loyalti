/**
 * @type: skeleton
 * name: user.itemView.smallerCard.skeleton
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia className={classes.media}>
        <Skeleton variant="avatar" width={48} height={48} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="50%" />
        </ItemTitle>
        <Skeleton variant="text" width="120px" />
      </ItemText>
    </ItemView>
  );
}
