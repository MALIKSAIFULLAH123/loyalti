/**
 * @type: dialog
 * name: ui.dialog.prompt
 */
import { useGlobal } from '@metafox/framework';
import { ConfirmParams, TModalDialogProps } from '@metafox/dialog';
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField
} from '@mui/material';
import { assign } from 'lodash';
import React from 'react';

interface Props extends ConfirmParams, TModalDialogProps {
  label: string;
  helperText?: string;
  value?: string;
}

export default function PromptDialog({
  title,
  message,
  label,
  value,
  helperText,
  positiveButton,
  negativeButton
}: Props) {
  const { i18n, useDialog } = useGlobal();
  const { setDialogValue, disableBackdropClick, dialogProps } = useDialog();

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

  title = title ?? i18n.formatMessage({ id: 'prompt' });

  const [inputValue, setInputValue] = React.useState<string>(value ?? '');

  const onSubmit = () => {
    setDialogValue(inputValue);
  };

  return (
    <Dialog
      {...dialogProps}
      fullScreen={false}
      data-testid="popupConfirm"
      maxWidth="xs"
      role="alertdialog"
      fullWidth
      aria-modal
      variant="alert"
    >
      <DialogTitle id="dialogTitle" data-testid="popupTitle">
        {title ?? 'Prompt'}
      </DialogTitle>
      <DialogContent dividers={false}>
        <TextField
          onChange={evt => setInputValue(evt.currentTarget.value)}
          autoFocus
          defaultValue={value}
          fullWidth
          required
          placeholder={label}
          InputLabelProps={{ shrink: false }}
          variant="outlined"
          helperText={helperText}
        />
      </DialogContent>
      <DialogActions>
        <Button
          {...positiveRest}
          role="button"
          data-testid="buttonCancel"
          tabIndex={0}
          variant="contained"
          size="medium"
          color="primary"
          disabled={!inputValue.trim().length}
          onClick={onSubmit}
          sx={{ minWidth: 120 }}
        >
          {positiveLabel}
        </Button>
        <Button
          {...negativeRest}
          role="button"
          data-testid="buttonSubmit"
          tabIndex={1}
          variant="outlined"
          size="medium"
          color="primary"
          sx={{ minWidth: 120 }}
          onClick={onCancel}
        >
          {negativeLabel}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
