import { useGlobal } from '@metafox/framework';
import { Box, CircularProgress } from '@mui/material';
import { get, isString } from 'lodash';
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

const ErrorBoundary = ({
  loading,
  error,
  children,
  sx = {},
  loadingComponent = 'form.DefaultLoading',
  emptyComponent
}: any) => {
  const { jsxBackend } = useGlobal();

  if (loading) {
    if (loadingComponent)
      return jsxBackend.render({ component: loadingComponent });

    return (
      <div style={styles.backdrop}>
        <div style={styles.content}>
          <CircularProgress color="primary" size="3rem" />
        </div>
      </div>
    );
  }

  if (emptyComponent && !error)
    return isString(emptyComponent)
      ? jsxBackend.render({ component: emptyComponent })
      : emptyComponent;

  if (!error) return children;

  let statusCode = get(error, 'response.status');

  const message =
    get(error, 'response.data.error') ||
    get(error, 'response.data.message') ||
    get(error, 'response.data.meta.title');

  const content = get(error, 'response.data.meta.description');

  switch (statusCode) {
    case 404:
      statusCode = 404;
      break;
    case 403:
      statusCode = 403;
      break;
    default:
      statusCode = 404;
      break;
  }

  const ErrorPage = jsxBackend.render({
    component: `core.block.error${statusCode}`,
    props: {
      title: message,
      content
    }
  });

  return <Box sx={sx}>{ErrorPage}</Box>;
};

export default ErrorBoundary;
