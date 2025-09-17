import { Box, Typography } from '@mui/material';
import React from 'react';

export default function SlotHeader({ slotName, children }) {
  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between'
      }}
    >
      <Typography component="span" variant="body1">
        {slotName}
      </Typography>
      {children}
    </Box>
  );
}
