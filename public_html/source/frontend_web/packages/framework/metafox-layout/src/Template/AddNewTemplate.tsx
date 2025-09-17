/**
 * @type: dialog
 * name: layout.dialog.addNewTemplate
 */
import { useGlobal } from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import { Dialog } from '@mui/material';
import React from 'react';
import { createEmptyDialogFormSchema } from '../utils';

export default function AddNewTemplateDialog() {
  const { useDialog, i18n } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();
  const title = i18n.formatMessage({ id: 'add_new_template' });
  const formSchema = createEmptyDialogFormSchema(title);
  const initialValues = {};

  formSchema.elements.content.elements.name = {
    component: 'Text',
    name: 'name',
    variant: 'outlined',
    margin: 'normal',
    required: true,
    defaultValue: '',
    label: 'Template Name',
    fullWidth: true
  };

  formSchema.elements.footer.elements = {
    cancel: {
      component: 'Button',
      label: 'Cancel',
      color: 'primary',
      type: 'cancel',
      onClick: closeDialog
    },
    submit: {
      component: 'Button',
      label: 'Submit',
      color: 'primary',
      type: 'cancel',
      onClick: closeDialog
    }
  };

  const onSubmit = () => {};

  return (
    <Dialog {...dialogProps}>
      <FormBuilder
        dialog
        initialValues={initialValues}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
