/**
 * @type: dialog
 * name: photo.dialog.addPhotoAlbum
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { RemoteFormBuilder } from '@metafox/form';
import { APP_PHOTO, RESOURCE_ALBUM } from '@metafox/photo';
import React from 'react';

export default function AddPhotoAlbum({ id }) {
  const { useDialog, compactUrl } = useGlobal();
  const { dialogProps } = useDialog();
  const config = useResourceAction(APP_PHOTO, RESOURCE_ALBUM, 'addItemForm');

  if (!config) return null;

  return (
    <Dialog
      {...dialogProps}
      fullWidth
      maxWidth="xs"
      data-testid="addPhotoAlbum"
    >
      <RemoteFormBuilder
        dialog
        dataSource={{
          apiMethod: config.apiMethod,
          apiUrl: compactUrl(config.apiUrl, { id })
        }}
      />
    </Dialog>
  );
}
