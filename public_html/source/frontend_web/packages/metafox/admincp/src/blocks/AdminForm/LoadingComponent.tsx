/**
 * @type: ui
 * name: ui.admincp.loading
 */

import { Alert, CircularProgress, Box } from '@mui/material';
import { get } from 'lodash';
import React from 'react';

const styles: {
  backdrop: React.CSSProperties;
  content: React.CSSProperties;
} = {
  backdrop: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0
  },
  content: {
    position: 'absolute',
    left: '50%',
    top: '50%',
    marginLeft: -20,
    marginTop: -20
  }
};

export default function LoadingComponent({
  error,
  size = 40,
  minHeight = 100,
  noWrapper = false
}: {
  size?: number;
  error?: Record<string, any>;
  loading?: boolean;
  noWrapper?: boolean;
  minHeight?: number;
}) {
  const msg =
    get(error, 'response.data.message') || get(error, 'message') || error;

  if (noWrapper) {
    return <Alert color="error" children={msg?.toString()} />;
  }

  return (
    <Box sx={{ minHeight }}>
      <div style={styles.backdrop} data-testid="loadingIndicator">
        <div style={styles.content}>
          {error ? (
            <Alert variant="filled" color="error" children={msg?.toString()} />
          ) : (
            <CircularProgress color="primary" size="3rem" />
          )}
        </div>
      </div>
    </Box>
  );
}
