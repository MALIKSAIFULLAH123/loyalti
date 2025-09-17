import React from 'react';
import { Box } from '@mui/material';

export default function PanelContent({ children, ...rest }) {
  return (
    <Box
      component="div"
      sx={{
        px: 2,
        py: 2,
        flex: 1,
        position: 'relative',
        display: 'flex',
        flexDirection: 'column'
      }}
      {...rest}
    >
      {children}
    </Box>
  );
}
