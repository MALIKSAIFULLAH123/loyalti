/**
 * @type: skeleton
 * name: user.itemView.reviewPosts.skeleton
 */
import { ItemView } from '@metafox/ui';
import { Box, Card, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const RootFormStyled = styled(Card)(({ theme }) => ({
  padding: theme.spacing(2),
  marginBottom: theme.spacing(2),
  boxShadow: 'none',
  height: '100px',
  display: 'flex',
  alignItems: 'center'
}));

export function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView testid="loadingSkeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <Box sx={{ pt: 2, display: 'flex' }}>
        <Skeleton variant="circular" width={40} height={40} />
        <Box sx={{ pl: 1, flex: 1, minWidth: 0 }}>
          <Skeleton variant="text" component="div" />
          <Skeleton variant="text" width={120} />
        </Box>
      </Box>
      <Box>
        <Box sx={{ height: 100 }}></Box>
        <Box
          sx={{
            display: 'flex'
          }}
        >
          <Skeleton variant="text" width={100} sx={{ mr: 1 }} />
          <Skeleton variant="text" width={100} />
        </Box>
      </Box>
    </ItemView>
  );
}

export function LoadingFormSkeleton() {
  return (
    <RootFormStyled>
      <Skeleton animation="wave" height={35} width="100%" />
    </RootFormStyled>
  );
}

export default LoadingSkeleton;
