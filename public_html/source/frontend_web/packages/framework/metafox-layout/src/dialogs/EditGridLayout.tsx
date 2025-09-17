/**
 * @type: dialog
 * name: layout.dialog.EditGridLayout
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { Dialog, useTheme } from '@mui/material';
import { cloneDeep } from 'lodash';
import React from 'react';
import { createFeatures } from './createFeatures';

export default function EditGridLayout({ styleName }) {
  const manager = useGlobal();
  const theme = useTheme();
  const { useDialog, layoutBackend, i18n } = manager;
  const dialogItem = useDialog();
  const { dialogProps, disableBackdropClick } = dialogItem;

  React.useEffect(() => {
    disableBackdropClick(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const initialValues = cloneDeep(layoutBackend.getGridPreset(styleName)) || {};
  initialValues.styleName = styleName;

  const formSchema: FormSchemaShape = {
    component: 'Form',
    dialog: true,
    title: `Edit: ${styleName}`,
    elements: {
      content: {
        component: 'Container',
        elements: {}
      },
      footer: {
        component: 'FormFooter',
        elements: {
          submit: {
            name: 'submit',
            type: 'submit',
            component: 'Button',
            variant: 'contained',
            color: 'primary',
            margin: 'none',
            label: i18n.formatMessage({ id: 'save_changes' })
          },
          cancel: {
            component: 'Cancel',
            label: i18n.formatMessage({ id: 'cancel' }),
            color: 'primary',
            variant: 'outlined',
            margin: 'none'
          }
        }
      }
    }
  };

  createFeatures('editGridLayout', formSchema, {
    manager,
    features: [],
    disabled: {},
    extra: {},
    theme,
    config: {}
  });

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <FormBuilder
        dialog
        dialogItem={dialogItem}
        submitAction="@layout/saveGridLayout"
        initialValues={initialValues}
        formSchema={formSchema}
      />
    </Dialog>
  );
}
