/**
 * @type: ui
 * name: form.DialogLoadingComponent
 */
import React from 'react';
import { CircularProgress, Box } from '@mui/material';

export default function DefaultLoading() {
  return (
    <Box
      sx={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        minHeight: '300px',
        minWidth: '300px'
      }}
    >
      <CircularProgress size={32} />
    </Box>
  );
}
