/**
 * @type: ui
 * name: app.step.DownloadWizard
 * bundle: admincp
 */
import { useGlobal } from '@metafox/framework';
import { useStepNavigate } from '@metafox/ui/steps';
import { Button, LinearProgress, Typography } from '@mui/material';
import { Box } from '@mui/system';
import { get } from 'lodash';
import React from 'react';

export default function DownloadWizard() {
  const { i18n, getErrorMessage, apiClient } = useGlobal();
  const [loading, setLoading] = React.useState<boolean>(true);
  const [error, setError] = React.useState();
  const [next] = useStepNavigate();

  const fetchOrRetry = React.useCallback(
    () =>
      apiClient
        .get('/admincp/app/upgrade/download-framework', {
          timeout: 10e3
        })
        .then(res => {
          if (get(res, 'data.data.retry')) {
            setTimeout(() => fetchOrRetry(), 5e3);
          } else {
            setLoading(false);
          }
        })
        .catch(err => {
          if (err.code === 'ECONNABORTED') {
            setTimeout(() => fetchOrRetry(), 5e3);
          } else {
            setLoading(false);
            setError(err);
          }
        }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  React.useEffect(() => {
    fetchOrRetry();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Box>
      {loading ? (
        <Box
          sx={{
            py: 2,
            display: 'flex',
            flexDirection: 'row',
            alignItems: 'center'
          }}
        >
          <Box>
            <Typography>Downloading ...</Typography>
          </Box>
          <Box sx={{ width: 200, pb: 2, pl: 2 }}>
            <LinearProgress variant="indeterminate" color="success" />
          </Box>
        </Box>
      ) : null}
      {error ? (
        <Box>
          <Typography sx={{ whiteSpace: 'pre-wrap' }} color="error">
            {getErrorMessage(error)}
          </Typography>
        </Box>
      ) : null}
      {!loading && !error ? (
        <Box>
          <Typography sx={{ whiteSpace: 'pre-wrap' }}>
            Downloaded new version successfully.
          </Typography>
        </Box>
      ) : null}
      {!loading && !error ? (
        <Box sx={{ pt: 2 }}>
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
      ) : null}
    </Box>
  );
}
