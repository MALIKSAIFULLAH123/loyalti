/**
 * @type: dialog
 * name: core.dialog.alertReminderItem
 */
import { useGlobal } from '@metafox/framework';
import { AlertParams, TModalDialogProps, DialogTitle } from '@metafox/dialog';
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText
} from '@mui/material';
import { assign, isString } from 'lodash';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { HtmlViewerWrapper } from '@metafox/ui';

export interface Props extends AlertParams, TModalDialogProps {}

export default function AlertDialog({
  title,
  message,
  positiveButton,
  maxWidth = 'sm',
  disableClose = false,
  onClose
}: Props) {
  const { useDialog, i18n } = useGlobal();
  const { setDialogValue, disableBackdropClick, dialogProps, closeDialog } =
    useDialog();

  const onOk = () => setDialogValue(true);

  const onCancel = () => {
    closeDialog();
    onClose && onClose();
  };

  React.useEffect(() => {
    disableBackdropClick(true);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const { label: positiveLabel, ...positiveRest } = assign(
    {
      label: i18n.formatMessage({ id: 'ok' }),
      color: 'primary'
    },
    positiveButton
  );

  title = title ?? i18n.formatMessage({ id: 'alert' });

  return (
    <Dialog
      {...dialogProps}
      fullScreen={false}
      data-testid="popupAlert"
      role="alertdialog"
      maxWidth={maxWidth}
      fullWidth
      aria-modal
      disableEscapeKeyDown
      variant="alert"
    >
      <DialogTitle
        children={title ?? 'Alert'}
        disableClose={disableClose}
        onCancel={onCancel}
      />
      <DialogContent dividers={false}>
        {isString(message) ? (
          <DialogContentText
            id="dialogDescription"
            data-testid="popupMessage"
            paragraph={false}
          >
            <HtmlViewerWrapper mt={0}>
              <HtmlViewer html={message} />
            </HtmlViewerWrapper>
          </DialogContentText>
        ) : (
          message
        )}
      </DialogContent>
      <DialogActions>
        <Button
          data-testid="buttonSubmit"
          role="button"
          id="buttonSubmit"
          autoFocus
          color="primary"
          size="medium"
          variant="contained"
          onClick={onOk}
          sx={{ minWidth: 120 }}
          disableRipple
          {...positiveRest}
        >
          {positiveLabel}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
