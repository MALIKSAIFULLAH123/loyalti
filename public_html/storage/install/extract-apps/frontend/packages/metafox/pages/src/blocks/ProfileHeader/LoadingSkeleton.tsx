import { Container } from '@metafox/ui';
import { Skeleton, Box } from '@mui/material';
import React from 'react';
import useStyles from './SkeletonStyles';

export function LoadingSkeleton() {
  const classes = useStyles();

  return (
    <Container maxWidth="md" gutter>
      <Box sx={{ background: theme => theme.palette.background.paper }}>
        <Skeleton
          variant="rectangular"
          sx={{ height: 320 }}
          animation={false}
        />
      </Box>
      <div className={classes.profileUserWrapper}>
        <div className={classes.userInfo}>
          <div className={classes.avatarWrapper}>
            <div className={classes.avatar}></div>
          </div>
          <div className={classes.name}>
            <Skeleton variant="text" width={200} height={42} />
            <Skeleton variant="text" width={'50%'} />
          </div>
          <div className={classes.btn}>
            <Skeleton variant="button" width={272} height={32} />
          </div>
        </div>
      </div>
      <div className={classes.menu}>
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <div className={classes.btnGroups}>
          <Skeleton variant="button" width={96} height={32} />
          <Skeleton variant="button" width={32} height={32} />
          <Skeleton variant="button" width={32} height={32} />
        </div>
      </div>
    </Container>
  );
}
