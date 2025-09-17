/**
 * @type: dialog
 * name: share.dialog.shareItem
 */

import { useGlobal } from '@metafox/framework';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  useDialog
} from '@metafox/dialog';
import { UserAvatar } from '@metafox/ui';
import { Button } from '@mui/material';
import React from 'react';
type Props = {};

export default function ShareItemDialog(props: Props) {
  const { i18n, useSession } = useGlobal();
  const { user } = useSession();
  const { dialogProps } = useDialog();

  return (
    <Dialog {...dialogProps}>
      <DialogTitle>{i18n.formatMessage({ id: 'share' })}</DialogTitle>
      <DialogContent sx={{ minWidth: 600, maxWidth: '100%' }}>
        <div>
          <UserAvatar user={user} size={40} noLink />
        </div>
        This features is coming soon!
      </DialogContent>
      <DialogActions>
        <Button
          style={{ minWidth: 160 }}
          variant="contained"
          color="primary"
          size="medium"
        >
          {i18n.formatMessage({ id: 'share' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
