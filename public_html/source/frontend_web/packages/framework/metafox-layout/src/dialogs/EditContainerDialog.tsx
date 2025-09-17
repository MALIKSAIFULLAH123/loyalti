/**
 * @type: dialog
 * name: layout.dialog.editContainer
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { EditContainerParams } from '@metafox/layout/types';
import {
  selectMarginField,
  selectPaddingField,
  selectSlotMinHeightField
} from '@metafox/layout/utils';
import { Dialog, Theme, useTheme } from '@mui/material';
import { range } from 'lodash';
import React from 'react';

const createFormSchema = (theme: Theme, title: string): FormSchemaShape => {
  const maxWidthOptions = [
    { value: 'none', label: 'Fluid, take full width of parent element' }
  ];
  const points = theme.breakpoints.values;

  Object.keys(points)
    .filter(v => 0 < points[v])
    .forEach(k => {
      maxWidthOptions.push({ value: k, label: `${k} - ${points[k]} px` });
    });

  const spacingOptions = [{ value: '', label: 'No' }];

  range(1, 2).forEach(v => {
    spacingOptions.push({
      value: v.toString(),
      label: `${theme.spacing(v)}`
    });
  });

  return {
    component: 'Form',
    title,
    elements: {
      content: {
        component: 'Container',
        elements: {
          maxWidth: {
            component: 'Dropdown',
            name: 'rootStyle.maxWidth',
            label: 'Max Width - Determine the max-width of the container',
            description:
              'The container width grows with the size of the screen.',
            defaultValue: 'none',
            fullWidth: true,
            margin: 'normal',
            options: maxWidthOptions
          },
          minHeight: selectSlotMinHeightField(null, {
            name: 'rootStyle.minHeight'
          }),
          padding: selectPaddingField(null, {
            prefix: 'rootStyle.sx',
            description: 'Gutter - Space inside container'
          }),
          margin: selectMarginField(null, {
            prefix: 'rootStyle.sx',
            description: 'Margin - Space outside container'
          })
        }
      },
      footer: {
        component: 'FormFooter',
        elements: {
          submit: {
            type: 'submit',
            component: 'Submit',
            variant: 'contained',
            label: 'Save Changes',
            color: 'primary'
          },
          cancel: {
            component: 'Cancel',
            label: 'Cancel',
            type: 'cancel'
          }
        }
      }
    }
  };
};

export default function EditContainerDialog(props: EditContainerParams) {
  const { templateName, containerName, elementPath } = props;
  const { useDialog, layoutBackend, i18n } = useGlobal();
  const dialogItem = useDialog();
  const { closeDialog, dialogProps } = dialogItem;
  const theme = useTheme();
  const config = layoutBackend.getContainerConfig({
    templateName,
    elementPath,
    containerName
  });

  const initialValues = config;

  const onSubmit = values => {
    layoutBackend.setContainerConfig(props, () => values);
    closeDialog();
  };

  const title = i18n.formatMessage({ id: 'edit_layout_container' });

  const formSchema = createFormSchema(theme, title);

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <FormBuilder
        dialog
        dialogItem={dialogItem}
        initialValues={initialValues}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
