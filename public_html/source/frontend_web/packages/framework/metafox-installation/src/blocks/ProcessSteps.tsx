/**
 * @type: block
 * name: install.Processing
 * bundle: installation
 */
import React from 'react';
import { createBlock, useGlobal } from '@metafox/framework';
import { Box, Button, LinearProgress, Typography } from '@mui/material';
import {
  HelpBlock,
  InstallMenu,
  Panel,
  PanelBody,
  PanelContent,
  PanelHeader,
  ReportIcon,
  useInstallationState
} from '@metafox/installation';
import { get } from 'lodash';
import { ProcessStepShape } from '../types';
import { getErrString } from '@metafox/utils';

function ProcessStep({
  step,
  active,
  onSuccess
}: {
  step: ProcessStepShape;
  active: boolean;
  onSuccess: () => void;
}) {
  const { apiClient, dialogBackend } = useGlobal();
  const { title, dataSource } = step;
  const initialValues = useInstallationState();
  const [error, setError] = React.useState();
  const [success, setSuccess] = React.useState<boolean>(false);

  const data = step.data
    ? step.data
    : { ...initialValues, recommendApps: undefined };

  const fetchOrRetry = React.useCallback(() => {
    apiClient
      .request({
        timeout: 10000,
        url: dataSource.apiUrl,
        method: dataSource.apiMethod,
        data
      })
      .then(res => {
        if (get(res, 'data.data.retry')) {
          setTimeout(() => fetchOrRetry(), 5e3);
        } else {
          setSuccess(true);
          onSuccess();
        }
      })
      .catch(err => {
        if (err.code === 'ECONNABORTED') {
          fetchOrRetry();
        } else {
          dialogBackend.alert({ title: 'Oops', message: getErrString(err) });
          setError(err);
        }
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (active) {
      fetchOrRetry();
    }
  }, [active]);

  const handleRetry = () => {
    fetchOrRetry();
    setError(undefined);
    setSuccess(undefined);
  };

  return (
    <Box
      sx={{
        display: 'flex',
        flexDirection: 'row',
        flex: 1,
        py: 1,
        alignItems: 'center'
      }}
    >
      <Box sx={{ flex: 1 }}>
        <Typography
          variant="body1"
          component="span"
          fontWeight={active ? 700 : undefined}
        >
          {title}
        </Typography>
      </Box>
      <Box
        sx={{
          width: 120,
          textAlign: 'right',
          height: 24,
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          justifyContent: 'flex-end'
        }}
      >
        {active && !error ? (
          <Box sx={{ pt: '8px', width: 120 }}>
            <LinearProgress variant="indeterminate" color="warning" />
          </Box>
        ) : null}
        {success ? (
          <Typography variant="body1" component="span">
            <ReportIcon variant="success" />
          </Typography>
        ) : null}
        {error ? (
          <Button size="small" color="primary" onClick={handleRetry}>
            Retry
          </Button>
        ) : null}
        {error ? (
          <Typography variant="body1" component="span">
            <ReportIcon variant="error" />
          </Typography>
        ) : null}
      </Box>
    </Box>
  );
}

function ProcessSteps({ dataSource }) {
  const { dispatch } = useGlobal();
  const { processList: steps } = useInstallationState();
  const [currentIndex, setStepIndex] = React.useState<number>(0);

  const next = React.useCallback(() => {
    dispatch({ type: '@install/next', payload: {} });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (steps.length > 0 && currentIndex >= steps.length) {
      next();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentIndex]);

  React.useEffect(() => {
    dispatch({ type: '@install/process-list/fetch', payload: dataSource });
    window.onbeforeunload = () => {
      return 'Do you want to skip this installation process';
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Panel>
      <PanelHeader />
      <PanelBody hasMenu>
        <InstallMenu />
        <PanelContent data-testid="blockProcessInstall">
          {!steps.length ? (
            <Box component="div" data-testid="loadingIndicator">
              <LinearProgress color="primary" variant="indeterminate" />
            </Box>
          ) : null}
          {steps.map((step, index) => (
            <ProcessStep
              step={step}
              key={index.toString()}
              onSuccess={() => setStepIndex(index + 1)}
              active={currentIndex === index}
            />
          ))}
          <HelpBlock />
        </PanelContent>
      </PanelBody>
    </Panel>
  );
}

export default createBlock({
  extendBlock: ProcessSteps
});
