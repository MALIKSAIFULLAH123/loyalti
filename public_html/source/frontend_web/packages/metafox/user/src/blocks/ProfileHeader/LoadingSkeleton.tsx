import { Container } from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';
import useStyles from './SkeletonStyles';

export function LoadingSkeleton() {
  const classes = useStyles();

  return (
    <Container maxWidth="md" gutter>
      <Skeleton
        variant="rectangular"
        sx={{ height: { sm: 320, xs: 179 } }}
        animation={false}
      />
      <div className={classes.profileUserWrapper}>
        <div className={classes.userInfo}>
          <div className={classes.avatarWrapper}>
            <div className={classes.avatar}></div>
          </div>
          <div className={classes.name}>
            <Skeleton variant="text" width={200} height={32} />
          </div>
        </div>
      </div>
      <div className={classes.menu}>
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
      </div>
    </Container>
  );
}
