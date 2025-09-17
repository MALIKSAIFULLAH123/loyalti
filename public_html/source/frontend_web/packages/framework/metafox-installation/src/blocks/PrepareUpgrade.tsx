/**
 * @type: block
 * name: install.PrepareUpgrade
 * bundle: installation
 */
import { createBlock, useGlobal } from '@metafox/framework';
import {
  HelpBlock,
  InstallMenu,
  Panel,
  PanelBody,
  PanelContent,
  PanelFooter,
  PanelHeader
} from '@metafox/installation/components';
import { Box, Button, Link, Typography } from '@mui/material';
import React from 'react';
import { useInstallationState } from '../hooks';

function Body() {
  const { baseUrl } = useInstallationState();

  return (
    <>
      <Typography variant="h3" paragraph>
        Prepare for upgrading
      </Typography>
      <Box sx={{ mt: 2 }}>
        <Typography>
          Before upgrading please &nbsp;
          <Link
            href={`${baseUrl}/admincp/backup/file/browse`}
            target="_blank"
            color="primary"
            sx={{ fontWeight: 'bold' }}
          >
            backup your site
          </Link>
          .
        </Typography>
      </Box>
    </>
  );
}

function PrepareUpgrade() {
  const { dispatch } = useGlobal();

  const handleContinue = () => {
    dispatch({ type: '@install/next', payload: {} });
  };

  return (
    <Panel>
      <PanelHeader />
      <PanelBody hasMenu>
        <InstallMenu />
        <PanelContent data-testid="blockInstallCompleted">
          <Body />
          <HelpBlock />
        </PanelContent>
      </PanelBody>
      <PanelFooter>
        <Button
          color="primary"
          variant="contained"
          onClick={handleContinue}
          data-testid="buttonContinue"
        >
          Continue
        </Button>
      </PanelFooter>
    </Panel>
  );
}

export default createBlock({
  extendBlock: PrepareUpgrade
});
