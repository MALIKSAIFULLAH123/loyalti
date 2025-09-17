import { APP_BOOTSTRAP, useGlobal } from '@metafox/framework';
import { Button, ButtonProps, CircularProgress } from '@mui/material';
import { AxiosRequestConfig } from 'axios';
import React, { useCallback, useState } from 'react';

export default function RemoteApiButton({
  children,
  apiUrl,
  apiMethod,
  bootstrap,
  ...rest
}: ButtonProps & {
  apiUrl: string;
  apiMethod?: AxiosRequestConfig['method'];
  bootstrap?: boolean;
}) {
  const [loading, setLoading] = useState<boolean>(false);
  const { apiClient, dispatch } = useGlobal();

  const handleClick = useCallback(() => {
    if (loading) return;

    setLoading(true);

    const send = () => {
      apiClient
        .request({
          url: apiUrl,
          method: apiMethod
        })
        .finally(() => {
          setLoading(false);

          if (bootstrap) {
            dispatch({ type: APP_BOOTSTRAP });
          }
        });
    };
    send();
  }, [apiClient, apiMethod, apiUrl, bootstrap, dispatch, loading]);

  return (
    <Button disabled={loading} onClick={handleClick} {...rest}>
      {children}{' '}
      {loading ? (
        <CircularProgress size={14} variant="indeterminate" color="secondary" />
      ) : null}
    </Button>
  );
}
