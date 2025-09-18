/**
 * @type: dialog
 * name: video.dialog.AddVideoToStatusComposerDialog
 */

import { useGlobal, useResourceAction } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import React from 'react';
import { APP_VIDEO } from '@metafox/video/constant';

export default function AddPollDialog({ initialValues }) {
  const { useDialog } = useGlobal();
  const { dialogProps } = useDialog();
  const dataSource = useResourceAction(
    APP_VIDEO,
    APP_VIDEO,
    'composerShareItem'
  );

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <RemoteFormBuilder
        initialValues={initialValues}
        dataSource={dataSource}
        dialog
      />
    </Dialog>
  );
}
