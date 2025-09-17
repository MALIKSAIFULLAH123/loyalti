import { Typography } from '@mui/material';
import React from 'react';
import { useInstallationState } from '@metafox/installation/hooks';
import { Box } from '@mui/system';

export default function HelpBlock() {
  const { helpBlock } = useInstallationState();

  return (
    <>
      <Box sx={{ flex: 1 }}></Box>
      <Typography sx={{ py: 2, pr: 2 }} paragraph data-testid="helpBlock">
        <span dangerouslySetInnerHTML={{ __html: helpBlock }}></span>
      </Typography>
    </>
  );
}
