/**
 * @type: dialog
 * name: ui.dialog.confirm
 */
import {
  ConfirmParams,
  TModalDialogProps,
  DialogActions
} from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import {
  Button,
  Dialog,
  DialogContent,
  DialogContentText,
  DialogTitle
} from '@mui/material';
import { assign, isString } from 'lodash';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { HtmlViewerWrapper } from '@metafox/ui';

interface Props extends ConfirmParams, TModalDialogProps {}

export default function ConfirmDialog({
  title,
  message,
  positiveButton,
  negativeButton
}: Props) {
  const { i18n, useDialog } = useGlobal();
  const { setDialogValue, dialogProps, disableBackdropClick } = useDialog();

  const onSubmit = () => {
    setDialogValue(true);
  };

  const onCancel = () => {
    setDialogValue(false);
  };

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const { label: negativeLabel, ...negativeRest } = assign(
    {
      label: i18n.formatMessage({ id: 'cancel' }),
      color: 'primary'
    },
    negativeButton
  );

  const { label: positiveLabel, ...positiveRest } = assign(
    {
      label: i18n.formatMessage({ id: 'ok' }),
      color: 'primary'
    },
    positiveButton
  );

  title = title ?? i18n.formatMessage({ id: 'confirm' });

  return (
    <Dialog
      {...dialogProps}
      fullScreen={false}
      data-testid="popupConfirm"
      maxWidth="xs"
      fullWidth
      aria-expanded
      aria-modal
      variant="alert"
    >
      <DialogTitle id="dialogTitle" data-testid="popupTitle">
        {title ?? 'Confirm'}
      </DialogTitle>
      <DialogContent dividers={false}>
        <DialogContentText id="dialogDescription" data-testid="popupMessage">
          {isString(message) ? (
            <HtmlViewerWrapper mt={0}>
              <HtmlViewer html={message} />
            </HtmlViewerWrapper>
          ) : (
            message ?? i18n.formatMessage({ id: 'are_you_sure' })
          )}
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
          onClick={onSubmit}
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
          onClick={onCancel}
          sx={{ minWidth: 100 }}
        >
          {negativeLabel}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
