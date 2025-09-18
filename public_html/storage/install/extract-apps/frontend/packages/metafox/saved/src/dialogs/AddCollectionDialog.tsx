/**
 * @type: dialog
 * name: saved.dialog.addCollection
 */

import { Dialog, DialogContent } from '@metafox/dialog';
import { RemoteFormBuilderProps, SmartFormBuilder } from '@metafox/form';
import {
  AppResourceAction,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { APP_SAVED } from '@metafox/saved/constant';
import React from 'react';

export default function AddCollectionDialog() {
  const { useDialog } = useGlobal();
  const dialogItem = useDialog();
  const { dialogProps, closeDialog } = dialogItem;

  const config: AppResourceAction = useResourceAction(
    APP_SAVED,
    'saved_list',
    'addItem'
  );

  const LoadingComponent = (
    props: RemoteFormBuilderProps['loadingComponent']
  ) => <div />;

  return (
    <Dialog
      data-testid="saved.dialog.addCollection"
      {...dialogProps}
      maxWidth="sm"
    >
      <DialogContent sx={{ maxWidth: '100%', p: 0 }}>
        <SmartFormBuilder
          dialog
          dialogItem={dialogItem}
          dataSource={{ apiUrl: config.apiUrl }}
          onCancel={closeDialog}
          onSuccess={closeDialog}
          loadingComponent={<LoadingComponent />}
        />
      </DialogContent>
    </Dialog>
  );
}
