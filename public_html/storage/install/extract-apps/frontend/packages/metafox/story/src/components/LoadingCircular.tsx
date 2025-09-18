import { Box, CircularProgress, SxProps } from '@mui/material';
import React from 'react';

interface IProp {
  sx?: SxProps;
}

function LoadingCircular({ sx }: IProp) {
  return (
    <Box
      sx={{
        position: 'absolute',
        left: 0,
        right: 0,
        top: 0,
        bottom: 0,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'rgba(255,255,255,0.5)',
        zIndex: 2,
        ...sx
      }}
    >
      <CircularProgress size={16} style={{ color: '#fff' }} />
    </Box>
  );
}

export default LoadingCircular;
