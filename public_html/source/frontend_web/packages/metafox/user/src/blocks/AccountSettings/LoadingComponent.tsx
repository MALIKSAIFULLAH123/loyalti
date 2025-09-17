import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingComponent() {
  return (
    <Box data-testid="loadingIndicator">
      <Skeleton animation="wave" height={50} width="50%" />
      <Box
        sx={{
          display: 'flex',
          width: '150px',
          justifyContent: 'space-between'
        }}
      >
        <Skeleton height={50} width="70px" />
        <Skeleton height={50} width="70px" />
      </Box>
    </Box>
  );
}
