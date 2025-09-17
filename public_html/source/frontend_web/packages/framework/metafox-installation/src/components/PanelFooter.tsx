import React from 'react';
import { Box } from '@mui/material';

export default function PanelFooter({ children }: { children?: any }) {
  if (!children) return null;

  return (
    <Box
      sx={{
        py: 2,
        px: 2,
        textAlign: 'right',
        borderTop: theme => theme.mixins.border('secondary')
      }}
    >
      {children}
    </Box>
  );
}
