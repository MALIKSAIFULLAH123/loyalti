/**
 * @type: ui
 * name: form.DefaultLoading
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
        p: 2
      }}
    >
      <CircularProgress size={24} />
    </Box>
  );
}
