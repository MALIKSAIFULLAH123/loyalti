/**
 * This component handle global error wrapper whole site.
 */
import { Box, Typography, styled } from '@mui/material';
import { get } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';
type State = {
  error?: any;
  errorInfo?: any;
};

const ErrorImage = styled('img', {
  name: 'SiteError',
  slot: 'ErrorImage'
})(({ theme }) => ({
  maxWidth: '100%'
}));

const ERROR_CODE_ACCEPT = [427];

export default class SiteErrorBoundary extends React.Component<{}, State> {
  state: State = {};

  static getDerivedStateFromError(error) {
    return { error };
  }

  componentDidCatch(error, errorInfo) {
    this.setState({
      error,
      errorInfo
    });
  }

  render() {
    const { error } = this.state;

    if (error) {
      // trace error name for more information.
      // const name = error.name
      // try to clear localStorage
      if (localStorage) localStorage.clear();

      const errCode = get(error, 'response.status');
      const statusCode = ERROR_CODE_ACCEPT.includes(errCode);

      if (statusCode) {
        const message =
          get(error, 'response.data.error') ||
          get(error, 'response.data.message') ||
          get(error, 'response.data.meta.title') ||
          get(error, 'message');

        const content = get(error, 'response.data.meta.description');
        const imageError = get(error, 'response.data.image');
        const icon = get(error, 'response.data.icon') || 'ico-warning-circle-o';

        return (
          <Box
            sx={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              textAlign: 'center',
              margin: '10vh 20px'
            }}
          >
            <Box data-testid="error5xx">
              <Typography
                component="h2"
                variant="h3"
                fontWeight={500}
                color={'error.main'}
                sx={{ marginBottom: '16px' }}
              >
                {message}
              </Typography>
              {content ? (
                <Typography
                  paragraph
                  variant="body1"
                  sx={{ marginBottom: '16px' }}
                >
                  {content}
                </Typography>
              ) : null}
              {imageError ? (
                <ErrorImage src={imageError} alt={message} />
              ) : (
                <LineIcon
                  sx={{
                    fontSize: '80px',
                    color: theme => theme.palette.error.main
                  }}
                  icon={icon}
                />
              )}
            </Box>
          </Box>
        );
      }

      return (
        <Box
          sx={{
            margin: '5em 2em',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center'
          }}
        >
          <Box data-testid="error5xx">
            <Typography component="h1" variant="h4">
              Whoops,
              <br />
              Something went wrong :(
            </Typography>
            <Typography paragraph variant="body1" sx={{ paddingTop: '1em' }}>
              {error.message}
            </Typography>
          </Box>
        </Box>
      );
    }

    return this.props.children;
  }
}
