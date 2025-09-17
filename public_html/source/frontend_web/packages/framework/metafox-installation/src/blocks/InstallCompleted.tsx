/**
 * @type: block
 * name: install.InstallCompleted
 * bundle: installation
 */
import { createBlock } from '@metafox/framework';
import {
  Panel,
  PanelBody,
  PanelContent,
  PanelHeader
} from '@metafox/installation/components';
import { Alert, Box, Link, Typography } from '@mui/material';
import { useInstallationState } from '../hooks';
import React from 'react';

function Body() {
  const { baseUrl } = useInstallationState();

  return (
    <>
      <Typography variant="h3" paragraph>
        Installation Completed
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
    </>
  );
}

function CompleteInstall() {
  return (
    <Panel>
      <PanelHeader />
      <PanelBody>
        <PanelContent data-testid="blockInstallCompleted">
          <Body />
        </PanelContent>
      </PanelBody>
    </Panel>
  );
}

export default createBlock({
  extendBlock: CompleteInstall
});
