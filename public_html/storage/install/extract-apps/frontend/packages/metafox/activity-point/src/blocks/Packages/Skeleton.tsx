import React from 'react';
import { CircularProgress, Box } from '@mui/material';

const SkeletonLoading = () => {
  return (
    <Box
      sx={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'background.paper'
      }}
      width="100%"
      height="400px"
    >
      <CircularProgress />
    </Box>
  );
};

export default SkeletonLoading;
