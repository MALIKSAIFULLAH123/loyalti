import { Box, Skeleton } from '@mui/material';
import React from 'react';

export default function LoadingSkeleton() {
  return (
    <Box>
      <Skeleton width="30%" />
      <Skeleton height={'80px'} width="100%" />
    </Box>
  );
}
