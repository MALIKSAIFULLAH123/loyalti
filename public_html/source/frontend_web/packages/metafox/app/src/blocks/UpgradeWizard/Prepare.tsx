/**
 * @type: ui
 * name: app.step.PrepareUpgrade
 * bundle: admincp
 */
import { Link, useGlobal } from '@metafox/framework';
import { Button, Typography } from '@mui/material';
import { Box } from '@mui/system';
import { useStepNavigate } from '@metafox/ui/steps';
import React from 'react';

export default function Prepare() {
  const { i18n } = useGlobal();
  const [next] = useStepNavigate();

  return (
    <Box>
      <Box sx={{ py: 2 }}>
        <Typography>Notice about backup before upgrade.</Typography>
        <Link
          variant="body2"
          target="_blank"
          color="primary"
          to="/backup/file/browse"
        >
          Backup
        </Link>
      </Box>
      <Box>
        <Button
          variant="contained"
          color="primary"
          size="small"
          data-testid="buttonSubmit"
          onClick={next}
        >
          {i18n.formatMessage({ id: 'Continue' })}
        </Button>
      </Box>
    </Box>
  );
}
