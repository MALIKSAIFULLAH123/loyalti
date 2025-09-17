/**
 * @type: dialog
 * name: layout.dialog.AddNewContainer
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { EditContainerParams } from '@metafox/layout/types';
import { Dialog } from '@mui/material';
import { range } from 'lodash';
import React from 'react';

export default function AddNewContainer(props: EditContainerParams) {
  const { useDialog, i18n, layoutBackend } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();
  const section = layoutBackend.getSectionConfig(props);

  const begin = '-1';
  const positionOptions = [{ value: begin, label: 'At the begin' }];

  Object.keys(section.elements).forEach((name, index) => {
    positionOptions.push({
      value: index.toString(),
      label: `After container "${name}"`
    });
  });

  const initialValues = { slots: 1, position: begin };
  const slotOptions = range(1, 5).map(x => ({ value: x, label: x.toString() }));

  const onSubmit = values => {
    layoutBackend.addNewContainer(
      props,
      parseInt(values.slots, 10),
      parseInt(values.position, 10)
    );
    closeDialog();
  };

  const formSchema: FormSchemaShape = {
    component: 'Form',
    title: i18n.formatMessage({ id: 'create_layout_container' }),
    elements: {
      content: {
        component: 'Container',
        elements: {
          slots: {
            component: 'Dropdown',
            name: 'slots',
            label: 'Number of slots',
            required: true,
            fullWidth: true,
            margin: 'normal',
            defaultValue: 1,
            displayEmpty: false,
            options: slotOptions
          },
          position: {
            component: 'Dropdown',
            name: 'position',
            label: 'Insert slot to',
            required: true,
            fullWidth: true,
            defaultValue: -1,
            displayEmpty: false,
            margin: 'normal',
            options: positionOptions
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
    <Dialog {...dialogProps} fullWidth maxWidth="sm">
      <FormBuilder
        dialog
        initialValues={initialValues}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
