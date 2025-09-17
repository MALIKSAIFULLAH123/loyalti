/**
 * @type: dialog
 * name: layout.dialog.InspectThemeDialog
 */

import { useGlobal } from '@metafox/framework';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { Button } from '@mui/material';
import React from 'react';

export default function ExportThemeVariables() {
  const { i18n, theme, useDialog } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle id="alert-dialog-title">
        {i18n.formatMessage({ id: 'layout_inspect_theme' })}
      </DialogTitle>
      <DialogContent>
        <pre>{JSON.stringify(theme, null, '  ')}</pre>
      </DialogContent>
      <DialogActions>
        <Button
          onClick={closeDialog}
          color="primary"
          autoFocus
          children={i18n.formatMessage({ id: 'ok' })}
        />
      </DialogActions>
    </Dialog>
  );
}
