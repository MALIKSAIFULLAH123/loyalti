/**
 * @type: ui
 * name: user.ManageShortcutButton
 * chunkName: sidebarHome
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton } from '@mui/material';
import React from 'react';

export default function EditButton() {
  const { dialogBackend } = useGlobal();

  const editShortcut = () => {
    dialogBackend.present({
      component: 'user.dialog.ManageShortcutDialog'
    });
  };

  return (
    <IconButton size="smaller" color="primary" onClick={editShortcut}>
      <LineIcon icon="ico-gear-o" color="primary" />
    </IconButton>
  );
}
