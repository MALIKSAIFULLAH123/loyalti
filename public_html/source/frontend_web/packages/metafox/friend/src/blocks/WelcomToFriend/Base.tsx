import { BlockViewProps, useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import * as React from 'react';

export default function Block({ title }: BlockViewProps) {
  const { i18n } = useGlobal();

  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        alignItems: 'center',
        px: 2,
        mt: 11.25
      }}
    >
      <LineIcon
        sx={{
          fontSize: '4.5rem',
          color: 'text.secondary',
          mb: 4
        }}
        icon="ico-user-circle-o"
      />
      <Typography variant="h3" sx={{ mb: 1.5 }}>
        {i18n.formatMessage({ id: 'no_profile_selected' })}
      </Typography>
      <Typography
        sx={{
          color: 'text.secondary',
          fontSize: theme => theme.typography.h4.fontSize
        }}
      >
        {i18n.formatMessage({ id: 'select_profile_to_review' })}
      </Typography>
    </Box>
  );
}
