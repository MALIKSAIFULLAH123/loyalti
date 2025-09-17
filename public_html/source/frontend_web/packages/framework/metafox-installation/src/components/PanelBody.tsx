import { Box } from '@mui/material';
import React from 'react';

export default function PanelBody({
  children,
  hasMenu
}: {
  children: any;
  hasMenu?: boolean;
}) {
  return (
    <Box
      sx={
        hasMenu
          ? { display: 'flex', minHeight: '400px', flexDirection: 'row' }
          : { minHeight: '400px' }
      }
    >
      {children}
    </Box>
  );
}
