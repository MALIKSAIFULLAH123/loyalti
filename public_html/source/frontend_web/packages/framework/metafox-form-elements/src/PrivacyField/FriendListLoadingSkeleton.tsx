import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './FriendListLoadingSkeleton.styles';

export default function FriendListLoadingSkeleton() {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <div className={classes.checkbox}>
        <Skeleton variant="rectangular" width={16} height={16} />
      </div>
      <Skeleton variant="text" width={'50%'} height={24} />
    </div>
  );
}
