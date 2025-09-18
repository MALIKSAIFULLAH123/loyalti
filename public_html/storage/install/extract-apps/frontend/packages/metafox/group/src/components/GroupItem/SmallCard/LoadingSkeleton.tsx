/**
 * @type: skeleton
 * name: groups.itemView.smallCard.skeleton
 * chunkName: group
 */
import { ItemView } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import * as React from 'react';
import useStyles from './styles';

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  const classes = useStyles();

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <div className={classes.outer}>
        <Skeleton variant="rectangular" height={72} width="100%" />
        <Skeleton variant="text" />
        <Skeleton variant="text" />
      </div>
    </ItemView>
  );
}
