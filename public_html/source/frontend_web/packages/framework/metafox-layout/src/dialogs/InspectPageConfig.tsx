/**
 * @type: dialog
 * name: layout.dialog.InspectPageDialog
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { Button } from '@mui/material';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import React from 'react';

export default function InspectPageDialog({ pageName }: { pageName: string }) {
  const { layoutBackend, useDialog, i18n } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="md">
      <DialogTitle id="alert-dialog-title">
        {i18n.formatMessage({ id: 'view_page_configure' })}
      </DialogTitle>
      <DialogContent>
        <textarea
          style={{ width: '100%' }}
          rows={25}
          readOnly
          defaultValue={JSON.stringify(
            layoutBackend.getPageConfig(pageName),
            null,
            '  '
          )}
        />
      </DialogContent>
      <DialogActions>
        <Button
          data-testid="buttonClose"
          onClick={closeDialog}
          color="primary"
          autoFocus
          children={i18n.formatMessage({ id: 'close' })}
        />
      </DialogActions>
    </Dialog>
  );
}
