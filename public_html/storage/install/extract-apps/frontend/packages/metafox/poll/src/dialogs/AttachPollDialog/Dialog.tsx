/**
 * @type: dialog
 * name: poll.dialog.AttachPollDialog
 */

import { useGlobal } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import { unset, cloneDeep } from 'lodash';
import React from 'react';

export default function AttachPollDialog({ initialValues, formUrl, isEdit }) {
  const { useDialog } = useGlobal();
  const { dialogProps, setDialogValue } = useDialog();

  const defaultValues = cloneDeep(initialValues);
  unset(defaultValues, 'submit');

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <RemoteFormBuilder
        initialValues={defaultValues}
        dataSource={{
          apiUrl: formUrl || '/poll/status-form',
          apiParams: { is_edit: isEdit }
        }}
        onSubmit={setDialogValue}
        dialog
      />
    </Dialog>
  );
}
