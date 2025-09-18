/**
 * @type: dialog
 * name: forum.dialog.FormDialog
 */

import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import React from 'react';

export default function FormDialog({ initialValues, formUrl }) {
  const { useDialog } = useGlobal();
  const dialogItem = useDialog();
  const { dialogProps } = dialogItem;

  if (!formUrl) return null;

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <RemoteFormBuilder
        initialValues={initialValues}
        dataSource={{ apiUrl: formUrl }}
        dialog
        dialogItem={dialogItem}
      />
    </Dialog>
  );
}
