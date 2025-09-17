import CheckIcon from '@mui/icons-material/Check';
import ErrorIcon from '@mui/icons-material/Error';
import { Box, Button, LinearProgress, Typography } from '@mui/material';
import { Step } from './types';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { get } from 'lodash';
import { getErrString } from '@metafox/utils';

export default function ProcessStep({
  step,
  active,
  onSuccess
}: {
  active: boolean;
  step: Step;
  onSuccess: () => void;
}) {
  const { apiClient, dialogBackend, i18n, useIsMobile } = useGlobal();
  const { dataSource, data } = step;
  const [error, setError] = React.useState();
  const [success, setSuccess] = React.useState<boolean>();
  const isMobile = useIsMobile();

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
          onSuccess();
          setSuccess(true);
        }
      })
      .catch(err => {
        if (err.code === 'ECONNABORTED') {
          fetchOrRetry();
        } else {
          const message = getErrString(err);
          dialogBackend.alert({ title: 'Oops!', message });
          setError(message);
        }
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (active) {
      fetchOrRetry();
    }
  }, [active, fetchOrRetry]);

  const handleRetry = () => {
    setError(undefined);
    fetchOrRetry();
  };

  return (
    <Box sx={{}}>
      <Box
        sx={{
          display: 'flex',
          flexDirection: 'row',
          alignItems: 'center',
          height: 40
        }}
      >
        <Box sx={{ display: 'flex', flex: 1 }}>
          <Typography variant={isMobile ? 'body2' : 'body1'}>
            {step.title}
          </Typography>
        </Box>
        {active && !error ? (
          <Box
            sx={{
              display: 'block',
              width: 220,
              maxWidth: isMobile ? '30%' : undefined
            }}
          >
            <LinearProgress variant="indeterminate" color="primary" />
          </Box>
        ) : null}
        {error ? (
          <Box>
            <ErrorIcon color="error" />
          </Box>
        ) : null}
        {error ? (
          <Button
            size="small"
            onClick={handleRetry}
            title={i18n.formatMessage({ id: 'retry' })}
          >
            {i18n.formatMessage({ id: 'retry' })}
          </Button>
        ) : null}
        {success ? (
          <Box>
            <CheckIcon color="success" />
          </Box>
        ) : null}
      </Box>
      <Box sx={{ pl: 2 }}>
        {step.message ? (
          <Typography sx={{ whiteSpace: 'pre-wrap' }}>
            {step.message}
          </Typography>
        ) : null}
      </Box>
    </Box>
  );
}
