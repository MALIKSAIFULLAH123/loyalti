/**
 * @type: block
 * name: install.MetaFoxRequirements
 * bundle: installation
 */
import { createBlock, useGlobal } from '@metafox/framework';
import React from 'react';
import {
  HelpBlock,
  InstallMenu,
  Panel,
  PanelBody,
  PanelContent,
  PanelFooter,
  PanelHeader,
  ReportIcon
} from '@metafox/installation/components';
import { Box, Button, Typography } from '@mui/material';
import { useInstallationState } from '../hooks';
import { RequireItem, RequireSection } from '../types';

const styles = {
  item: {
    display: 'flex',
    flexDirection: 'row',
    flex: 1,
    pb: 1.5,
    pr: 2,
    justifyContent: 'space-between',
    alignItems: 'center'
  }
};

const ReportItem = ({
  item,
  debug
}: {
  item: RequireItem;
  debug?: boolean;
}) => {
  if (item.value && item.skip) return null;

  return (
    <Box sx={styles.item} data-testid={item.label}>
      <Typography>{item.label}</Typography>
      <Typography>
        <ReportIcon variant={item.value ? 'success' : item.severity} />
      </Typography>
    </Box>
  );
};

const ReportSection = ({
  section,
  debug
}: {
  section: RequireSection;
  debug: boolean;
}) => {
  return (
    <>
      <Typography
        data-testid={section.title}
        variant="h3"
        sx={{ pt: 0.5, pb: 2 }}
      >
        {section.title}
      </Typography>
      {section.items.map((item, index) => (
        <ReportItem debug={debug} item={item} key={index.toString()} />
      ))}
    </>
  );
};

function MetaFoxRequirementBlock() {
  const { dispatch } = useGlobal();
  const { debug, requirement: data } = useInstallationState();

  const hasError = !data?.result;

  const handleContinue = () => {
    dispatch({ type: '@install/next', payload: {} });
  };

  return (
    <Panel>
      <PanelHeader />
      <PanelBody hasMenu>
        <InstallMenu />
        <PanelContent data-testid="blockRequirements">
          {data.sections.map((section, index) => {
            return (
              <ReportSection
                debug={debug}
                section={section}
                key={index.toString()}
              />
            );
          })}
          <HelpBlock />
        </PanelContent>
      </PanelBody>
      <PanelFooter>
        <Button
          color="primary"
          variant="contained"
          disabled={hasError}
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
  extendBlock: MetaFoxRequirementBlock,
  defaults: {
    title: 'System Requirements'
  }
});
