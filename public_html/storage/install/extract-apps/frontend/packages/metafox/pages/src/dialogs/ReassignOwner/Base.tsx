import { useGlobal } from '@metafox/framework';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { Button, Typography } from '@mui/material';
import React from 'react';

export default function Base() {
  const { useDialog, i18n } = useGlobal();

  const { dialogProps, closeDialog } = useDialog();

  return (
    <Dialog {...dialogProps}>
      <DialogTitle>{i18n.formatMessage({ id: 'Reassign Owner' })}</DialogTitle>
      <DialogContent>
        <Typography>
          Choose a friend and transfer this page to them. Remember that: Current
          owner will lose all admin privilege on this page after do this.
        </Typography>
      </DialogContent>
      <DialogActions>
        <Button
          autoFocus
          color="primary"
          onClick={closeDialog}
          data-testid="alertDialogOkButton"
        >
          {i18n.formatMessage({ id: 'Submit' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}

Base.displayName = 'ReassignOwnerDialog';
