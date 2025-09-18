/**
 * @type: ui
 * name: saved.ui.noCollectionList
 */

import { useGlobal } from '@metafox/framework';
import { Box, Typography } from '@mui/material';
import * as React from 'react';

export default function NoCollectionList() {
  const { i18n } = useGlobal();

  return (
    <Box mt={4} px={6}>
      <Typography
        variant="body1"
        color="text.hint"
        sx={{ textAlign: 'center' }}
      >
        {i18n.formatMessage({ id: 'no_description_collections_found' })}
      </Typography>
    </Box>
  );
}
