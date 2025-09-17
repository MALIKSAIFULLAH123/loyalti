/**
 * @type: ui
 * name: app.step.UpgradeCompleted
 * bundle: admincp
 */
import { Alert, Box, Link, Typography } from '@mui/material';
import React from 'react';

export default function UpgradeCompleted({ baseUrl }) {
  React.useEffect(() => {
    window.onbeforeunload = undefined;
  }, []);

  return (
    <Box sx={{ minHeight: 300, display: 'flex', flexDirection: 'column' }}>
      <Typography variant="h4" paragraph>
        Upgrade Completed
      </Typography>
      <Alert severity="error">
        Warning: The installation path (public/install) is still accessible.
        Please remove it to avoid security risks.
      </Alert>
      <Box sx={{ mt: 2 }}>
        <Typography>
          {'Enjoy your new '}
          <Link href={`${baseUrl}/`} color="primary">
            Social Network
          </Link>
          {' or configure your site in the '}
          <Link href={`${baseUrl}/admincp`} color="primary">
            AdminCP
          </Link>
          {' now'}
        </Typography>
      </Box>
      <Box sx={{ mt: 4 }}>
        <Typography component="div" sx={{ alignSelf: 'flex-end' }}>
          Your MetaFox is installed successfully.
        </Typography>
        <Typography component="div">MetaFox Team</Typography>
      </Box>
    </Box>
  );
}
