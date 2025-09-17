/**
 * @type: block
 * name: install.InstallFailed
 * bundle: installation
 */
import { createBlock } from '@metafox/framework';
import {
  Panel,
  PanelBody,
  PanelContent,
  PanelHeader
} from '@metafox/installation/components';
import React from 'react';
import { Typography } from '@mui/material';

function InstallFailed() {
  return (
    <Panel>
      <PanelHeader />
      <PanelBody>
        <PanelContent data-testid="blockInstallFailed">
          <Typography variant="h4" sx={{ pb: 2 }}>
            Installation Failed
          </Typography>
          <Typography variant="body1">
            Your MetaFox is installed failed. Checking log for more information.
          </Typography>
          <Typography variant="body1">MetaFox Team</Typography>
        </PanelContent>
      </PanelBody>
    </Panel>
  );
}

export default createBlock({
  extendBlock: InstallFailed
});
