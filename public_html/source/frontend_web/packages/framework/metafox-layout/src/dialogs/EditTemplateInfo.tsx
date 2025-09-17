/**
 * @type: dialog
 * name: layout.dialog.EditTemplateInfo
 * title: Edit Template Information
 * chunkName: layoutEditor
 */

import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { Dialog } from '@mui/material';
import React from 'react';

type Values = {
  title: string;
  description: string;
};

export default function EditTemplateInfo({ templateName }) {
  const { useDialog, i18n, layoutBackend } = useGlobal();
  const { closeDialog, setDialogValue, dialogProps } = useDialog();
  const config = layoutBackend.getTemplateConfig(templateName);

  const initialValues: Values = {
    title: config.title ?? templateName,
    description: config.description ?? ''
  };

  const onSubmit = (values: Values) => {
    setDialogValue(values);
    closeDialog();
  };

  const formSchema: FormSchemaShape = {
    component: 'Form',
    title: i18n.formatMessage({ id: 'edit_layout_template_info' }),
    elements: {
      content: {
        component: 'Container',
        elements: {
          title: {
            component: 'Text',
            name: 'title',
            label: 'Title',
            required: true,
            fullWidth: true,
            variant: 'outlined',
            margin: 'normal'
          },
          description: {
            component: 'Textarea',
            name: 'description',
            editor: false,
            label: 'Description',
            required: true,
            fullWidth: true,
            variant: 'outlined',
            margin: 'normal'
          }
        }
      },
      footer: {
        component: 'FormFooter',
        elements: {
          submit: {
            type: 'submit',
            component: 'Submit',
            label: 'Save Changes',
            color: 'primary'
          },
          cancel: {
            component: 'Cancel',
            label: 'Cancel',
            color: 'primary',
            type: 'cancel'
          }
        }
      }
    }
  };

  return (
    <Dialog {...dialogProps} maxWidth="sm">
      <FormBuilder
        dialog
        initialValues={initialValues}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
