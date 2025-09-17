/**
 * @type: ui
 * name: ui.toast.item
 */
import { useGlobal } from '@metafox/framework';
import { Alert, Button, Grow, styled } from '@mui/material';
import { isFunction } from 'lodash';
import React, { useCallback, useEffect, useState } from 'react';
import useStyles from './ToastItem.styles';
import { ToastItemShape } from './types';

const AlertStyled = styled(Alert, { name: 'AlertStyled' })(({ theme }) => ({
  '& .MuiAlert-action': {
    padding: theme.spacing(0),
    paddingLeft: theme.spacing(2),
    [theme.breakpoints.down('sm')]: {
      display: 'none'
    }
  }
}));

const CloseButton = styled(Button, { name: 'CloseButton' })(({ theme }) => ({
  padding: `${theme.spacing(1)} !important`,
  height: '100%',
  '.MuiAlert-filledInfo &': {
    color: theme.palette.grey['500']
  }
}));

export default function ToastItem({
  severity,
  message,
  duration,
  className,
  id,
  onClose
}: ToastItemShape) {
  const classes = useStyles();
  const [open, setOpen] = useState(true);
  const { i18n } = useGlobal();

  const handleClose = useCallback(() => {
    setOpen(false);

    isFunction(onClose) && onClose(id);
  }, [id, onClose]);

  useEffect(() => {
    if (duration === 'manual' || duration === 0) return;

    const timeout = window.setTimeout(handleClose, duration);

    return () => window.clearTimeout(timeout);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Grow in={open}>
      <AlertStyled
        variant="filled"
        classes={classes}
        severity={severity}
        icon={false}
        onClose={handleClose}
        className={className}
        data-testid="flashMessage"
        action={
          <CloseButton
            data-testid="btnClose"
            color="inherit"
            onClick={handleClose}
          >
            {i18n.formatMessage({ id: 'close' })}
          </CloseButton>
        }
      >
        {message}
      </AlertStyled>
    </Grow>
  );
}
