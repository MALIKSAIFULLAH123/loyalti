/**
 * @type: dialog
 * name: poll.dialog.AddPollToStatusComposerDialog
 * chunkName: dialog.addPollFeed
 */

import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import { unset } from 'lodash';
import React from 'react';

export default function AddPollDialog({ initialValues, isEdit }) {
  const { useDialog } = useGlobal();
  const { dialogProps, setDialogValue } = useDialog();

  // stop submit button util touched
  // FOXSOCIAL5-1324
  unset(initialValues, 'submit');

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <RemoteFormBuilder
        initialValues={initialValues}
        dataSource={{
          apiUrl: '/poll/status-form',
          apiParams: { is_edit: isEdit }
        }}
        onSubmit={setDialogValue}
        dialog
      />
    </Dialog>
  );
}
