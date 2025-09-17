import { Alert, Typography, Button, Box } from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';
import { LoadingButton } from '@mui/lab';
import HtmlViewer from '@metafox/html-viewer';

const Action = props => {
  const { name, title, action, payload, config = {}, hideAlert } = props;
  const { dispatch } = useGlobal();
  const [loading, setLoading] = React.useState(false);
  const handleClick = React.useCallback(() => {
    dispatch({
      type: action,
      payload,
      meta: {
        setLoading,
        hideAlert
      }
    });
  }, [action, dispatch, hideAlert, payload]);

  if (!action) return null;

  if (loading) {
    return (
      <LoadingButton
        role="button"
        autoFocus
        variant="contained"
        disableRipple
        size="small"
        color="primary"
        name={name}
        loading
        loadingPosition="start"
        {...config}
      >
        {title}
      </LoadingButton>
    );
  }

  return (
    <Button
      role="button"
      autoFocus
      variant="contained"
      disableRipple
      size="small"
      color="primary"
      name={name}
      {...config}
      onClick={handleClick}
    >
      {title}
    </Button>
  );
};

export default function AlertItem({
  severity,
  onClose,
  title,
  message,
  actions = []
}) {
  const [hidden, setHidden] = React.useState(false);

  const hideAlert = () => {
    setHidden(true);
  };

  if (hidden) return null;

  return (
    <Alert
      variant={'platform'}
      severity={severity}
      onClose={onClose}
      iconMapping={{
        success: <LineIcon icon={'ico-check-circle'} />,
        info: <LineIcon icon={'ico-question-circle'} />,
        error: <LineIcon icon={'ico-warning-circle'} />,
        warning: <LineIcon icon={'ico-warning-circle'} />
      }}
    >
      <Typography
        variant="body2"
        color="text.primary"
        fontWeight={700}
        mb={0.25}
      >
        {title}
      </Typography>
      <Typography variant="body2" color="text.secondary">
        <HtmlViewer html={message} />
      </Typography>
      {actions?.length ? (
        <Box mt={0.5}>
          {actions.map(action => (
            <Box key={action.name} sx={{ mr: 2 }} component="span">
              <Action {...action} hideAlert={hideAlert} />
            </Box>
          ))}
        </Box>
      ) : null}
    </Alert>
  );
}
