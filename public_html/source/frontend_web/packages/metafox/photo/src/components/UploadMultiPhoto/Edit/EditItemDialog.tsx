/**
 * @type: dialog
 * name: photo.dialog.quickEditPhotoFieldItem
 */

import { useGlobal, useResourceForm } from '@metafox/framework';
import { Dialog } from '@metafox/dialog';
import { SmartFormBuilder } from '@metafox/form';
import React from 'react';
import { APP_PHOTO, RESOURCE_PHOTO } from '@metafox/photo';

export default function FormDialog({
  initialValues,
  formSchema: formSchemaProp,
  formName = 'edit_selecting_photo',
  dataSource
}) {
  const { useDialog } = useGlobal();
  const dialogItem = useDialog();
  const { dialogProps, setDialogValue, disableBackdropClick } = dialogItem;
  const formSchema = useResourceForm(APP_PHOTO, RESOURCE_PHOTO, formName);
  const handleSubmit = React.useCallback(
    values => {
      setDialogValue(values);
    },
    [setDialogValue]
  );

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!formSchema && !dataSource) return null;

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <SmartFormBuilder
        pageParams={initialValues}
        initialValues={initialValues}
        dialog
        dialogItem={dialogItem}
        formSchema={formSchemaProp || formSchema}
        dataSource={dataSource}
        onSubmit={handleSubmit}
      />
    </Dialog>
  );
}
