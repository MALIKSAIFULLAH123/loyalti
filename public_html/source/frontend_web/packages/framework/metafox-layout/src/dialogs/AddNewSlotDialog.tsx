/**
 * @type: dialog
 * name: layout.addNewSlotDialog
 * chunkName: layoutEditor
 */
import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { EditContainerParams } from '@metafox/layout/types';
import { Dialog } from '@mui/material';
import { range } from 'lodash';
import React from 'react';

export default function AddNewSlotDialog(props: EditContainerParams) {
  const { useDialog, i18n, layoutBackend } = useGlobal();
  const { closeDialog, dialogProps } = useDialog();
  const config = layoutBackend.getContainerConfig(props);

  const begin = '-1';
  const positionOptions = [{ value: begin, label: 'At the begin' }];

  Object.keys(config.elements).forEach((name, index) => {
    positionOptions.push({
      value: index.toString(),
      label: `After slot "${name}"`
    });
  });

  const initialValues = {
    slots: 1,
    position: begin
  };

  const slotOptions = range(1, 6).map(x => ({
    value: x,
    label: x.toString()
  }));

  const onSubmit = values => {
    layoutBackend.addNewSlots(
      props,
      parseInt(values.slots, 10),
      parseInt(values.position, 10)
    );
    closeDialog();
  };

  const formSchema: FormSchemaShape = {
    component: 'Form',
    title: i18n.formatMessage({ id: 'create_layout_slot' }),
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
            displayEmpty: true,
            options: slotOptions
          },
          position: {
            component: 'Dropdown',
            name: 'position',
            label: 'Insert slot to',
            required: true,
            fullWidth: true,
            defaultValue: begin,
            displayEmpty: true,
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
    <Dialog {...dialogProps} maxWidth="xs">
      <FormBuilder
        dialog
        initialValues={initialValues}
        formSchema={formSchema}
        onSubmit={onSubmit}
      />
    </Dialog>
  );
}
