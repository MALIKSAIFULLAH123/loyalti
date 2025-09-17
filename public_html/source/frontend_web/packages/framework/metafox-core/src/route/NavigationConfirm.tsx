import {
  ConfirmParams,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { Button, DialogContentText } from '@mui/material';
import { assign } from 'lodash';
import React, { useEffect, useRef, useState } from 'react';
import Prompt from 'react-router-prompt';
type Callback = () => void;

export default function NavigationConfirm() {
  const { use, i18n } = useGlobal();

  const value = React.useRef<boolean>(false);
  const [when, setWhen] = useState(false);
  const [rev, setRev] = useState<number>(0);
  const confirm = useRef<ConfirmParams | boolean>();
  const onOk = useRef<Callback>();

  // confirm params
  const setNavigationConfirm = React.useCallback(
    (when: boolean, params: ConfirmParams | boolean, callbackFn: Callback) => {
      setWhen(when);
      setRev(rev => rev + 1);
      confirm.current = params;
      onOk.current = callbackFn;
    },
    []
  );

  useEffect(() => {
    use({
      setNavigationConfirm
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const { label: negativeLabel, ...negativeRest } = assign(
    {
      label: i18n.formatMessage({ id: 'cancel' }),
      color: 'primary'
    },
    confirm.current?.negativeButton
  );

  const { label: positiveLabel, ...positiveRest } = assign(
    {
      label: i18n.formatMessage({ id: 'ok' }),
      color: 'primary'
    },
    confirm.current?.positiveButton
  );

  const title = confirm.current?.title;
  const message = confirm.current?.message;

  const onExit = () => {
    if (!value.current) return;

    if (onOk.current) {
      onOk.current();
    }
  };

  // force key to mount prompt again when set navigation value.
  return (
    <Prompt when={when} key={rev.toString()}>
      {({ isActive, onConfirm, onCancel }) => {
        return (
          <Dialog
            open={isActive}
            fullScreen={false}
            data-testid="popupConfirm"
            maxWidth="xs"
            fullWidth
            aria-expanded
            TransitionProps={{
              onExit
            }}
            aria-modal
            variant="alert"
          >
            <DialogTitle id="dialogTitle" data-testid="popupTitle" disableClose>
              {title ?? i18n.formatMessage({ id: 'confirm' })}
            </DialogTitle>
            <DialogContent dividers={false}>
              <DialogContentText
                id="dialogDescription"
                data-testid="popupMessage"
              >
                {message ?? i18n.formatMessage({ id: 'are_you_sure' })}
              </DialogContentText>
            </DialogContent>
            <DialogActions>
              <Button
                {...positiveRest}
                data-testid="buttonSubmit"
                role="button"
                tabIndex={1}
                autoFocus
                variant="contained"
                disableRipple
                size="medium"
                color="primary"
                onClick={evt => {
                  value.current = true;
                  onConfirm(evt);
                }}
                sx={{ minWidth: 100 }}
              >
                {positiveLabel}
              </Button>
              <Button
                {...negativeRest}
                data-testid="buttonCancel"
                role="button"
                tabIndex={2}
                variant="text"
                disableRipple
                size="medium"
                color="primary"
                onClick={evt => {
                  value.current = false;
                  onCancel(evt);
                }}
                sx={{ minWidth: 100 }}
              >
                {negativeLabel}
              </Button>
            </DialogActions>
          </Dialog>
        );
      }}
    </Prompt>
  );
}
