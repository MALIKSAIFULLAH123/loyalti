import { Alert } from '@mui/material';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';

export default function Warning({ warning }: { warning?: string }) {
  if (!warning) return null;

  return (
    <Alert
      color="warning"
      variant="standard"
      sx={{ fontSize: 'small', my: 1, p: 0.5, alignItems: 'center' }}
    >
      <HtmlViewer html={warning || ''} />
    </Alert>
  );
}
