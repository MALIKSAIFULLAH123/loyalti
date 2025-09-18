import { Skeleton } from '@mui/material';
import useStyles from './FriendItemView.styles';
import React from 'react';

const LoadingSkeleton = () => {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <div className={classes.itemOuter}>
        <div className={classes.itemSmallInner}>
          <div className={classes.itemSmallMedia}>
            <div className={classes.imgSmallWrapper}>
              <Skeleton variant="circular" width={48} height={48} />
            </div>
          </div>
          <div className={classes.userSmallInfo}>
            <Skeleton variant="text" />
            <Skeleton variant="text" />
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoadingSkeleton;
