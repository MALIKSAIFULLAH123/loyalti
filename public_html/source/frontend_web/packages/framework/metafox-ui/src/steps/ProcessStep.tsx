import CheckIcon from '@mui/icons-material/Check';
import ErrorIcon from '@mui/icons-material/Error';
import { Box, Button, LinearProgress, Typography } from '@mui/material';
import { ProcessStepShape } from './types';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { get } from 'lodash';
import { getErrString } from '@metafox/utils';
import HtmlViewer from '@metafox/html-viewer';

export default function ProcessStep({
  step,
  active,
  onSuccess
}: {
  active: boolean;
  step: ProcessStepShape;
  onSuccess: () => void;
}) {
  const { apiClient, handleActionError, i18n } = useGlobal();
  const { dataSource, data, enableReport, params, dryRun } = step;
  const [status, setStatus] = React.useState<string>();
  const [report, setReport] = React.useState<string>();

  const method = dataSource?.apiMethod ?? 'GET';

  const fetchOrRetry = React.useCallback(() => {
    apiClient
      .request({
        timeout: step.disableUserAbort ? step.timeout ?? 10e3 : 0,
        url: dataSource.apiUrl,
        method,
        params,
        data
      })
      .then(res => {
        if (get(res, 'data.data.retry')) {
          setTimeout(() => fetchOrRetry(), 5e3);
        } else {
          setReport(get(res, 'data.data.message'));

          const status = get(res, 'data.data.status', 'success');

          setStatus(status ?? 'success');

          onSuccess();
        }
      })
      .catch(err => {
        if (dryRun) {
          setStatus('error');
          setReport(getErrString(err));
          onSuccess();
        } else if (err.code === 'ECONNABORTED') {
          fetchOrRetry();
        } else {
          handleActionError(err);
          setStatus('error');
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
    setStatus(undefined);
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
          <Typography fontWeight={active ? 700 : undefined}>
            {step.title}
          </Typography>
        </Box>
        {active && status !== 'error' ? (
          <Box sx={{ display: 'block', minWidth: 120, width: '30%' }}>
            <LinearProgress variant="indeterminate" color="primary" />
          </Box>
        ) : null}
        {status === 'error' ? (
          <Box>
            <ErrorIcon color="error" />
          </Box>
        ) : null}
        {status === 'error' && !step.disableRetry ? (
          <Button
            size="small"
            onClick={handleRetry}
            title={i18n.formatMessage({ id: 'retry' })}
          >
            {i18n.formatMessage({ id: 'retry' })}
          </Button>
        ) : null}
        {status === 'success' ? (
          <Box>
            <CheckIcon color="success" />
          </Box>
        ) : null}
      </Box>
      {enableReport && report ? (
        <HtmlViewer disableNl2br html={report} />
      ) : null}
      <Box></Box>
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
