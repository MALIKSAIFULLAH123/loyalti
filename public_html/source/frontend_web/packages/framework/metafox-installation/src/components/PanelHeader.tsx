import { Box, Typography } from '@mui/material';
import React from 'react';
import { useInstallationState } from '@metafox/installation/hooks';

export default function PanelHeader() {
  const { platformVersion } = useInstallationState();

  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        py: 2,
        px: 3,
        borderBottom: theme => theme.mixins.border('secondary')
      }}
    >
      <img
        alt="MetaFox"
        src={'https://metafox-assets.s3.eu-north-1.amazonaws.com/logo.png'}
        height={40}
      />
      <Typography>{platformVersion}</Typography>
    </Box>
  );
}
