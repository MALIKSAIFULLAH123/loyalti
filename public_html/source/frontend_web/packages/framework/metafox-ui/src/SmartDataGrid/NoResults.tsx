import React from 'react';
import { Box, Typography } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const NoResults = ({ message }) => {
  const { i18n } = useGlobal();

  message = message ?? i18n.formatMessage({ id: 'no_results_found' });

  return (
    <Box p={{ position: 'absolute', left: 16, top: 16 }}>
      <Typography>{message}</Typography>
    </Box>
  );
};

export default NoResults;
