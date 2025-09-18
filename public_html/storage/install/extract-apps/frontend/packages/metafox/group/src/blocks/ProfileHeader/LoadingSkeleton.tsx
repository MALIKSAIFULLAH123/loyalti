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
        <div className={classes.name}>
          <Skeleton variant="text" width={400} height={42} />
          <Skeleton variant="text" width={120} className={classes.type} />
        </div>
        <div className={classes.btn}>
          <Skeleton variant="button" width={140} height={32} />
        </div>
      </div>
      <div className={classes.menu}>
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <Skeleton variant="button" width={100} height={20} />
        <div className={classes.btnGroups}>
          <Skeleton variant="button" width={32} height={32} />
          <Skeleton variant="button" width={32} height={32} />
        </div>
      </div>
    </Container>
  );
}
