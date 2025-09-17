/**
 * @type: ui
 * name: error.fallback.some_thing_wrong
 */
import React from 'react';
import { IS_DEV } from '../constants';
import { useGlobal } from '@metafox/framework';
import { Box, styled } from '@mui/material';

export interface ErrorFallbackProps {
  error: Error;
  errorInfo: React.ErrorInfo;
}

const Content = styled(Box)(({ theme }) => ({
  whiteSpace: 'pre-line',
  display: 'flex',
  flexDirection: 'column'
}));

export default function ErrorFallback({
  error,
  errorInfo
}: ErrorFallbackProps) {
  const { i18n } = useGlobal();

  return (
    <div data-testid="errorBoundary">
      <Content sx={{ my: 3 }}>
        {i18n.formatMessage({ id: 'something_went_wrong' })}
      </Content>
      {IS_DEV ? (
        <details style={{ whiteSpace: 'pre-wrap' }}>
          {error && error.toString()}
          <br />
          {errorInfo && errorInfo.componentStack}
        </details>
      ) : null}
    </div>
  );
}
