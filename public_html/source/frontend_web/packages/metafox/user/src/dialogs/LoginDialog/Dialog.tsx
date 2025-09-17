/**
 * @type: dialog
 * name: user.dialog.LoginDialog
 */

import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import React from 'react';
import LoginForm from '@metafox/user/blocks/LoginContent/LoginForm';
import { Box, IconButton } from '@mui/material';
import { LineIcon } from '@metafox/ui';

export default function LoginDialog({ title, formName = 'login_popup' }) {
  const { useDialog } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <Box py={3}>
        <IconButton
          sx={{ position: 'absolute', top: 8, right: 8 }}
          onClick={closeDialog}
        >
          <LineIcon icon={'ico-close'} />
        </IconButton>
        <LoginForm
          submitAction="@loginFromDialog"
          formName={formName}
          title={title}
        />
      </Box>
    </Dialog>
  );
}
