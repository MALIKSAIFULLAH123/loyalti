/**
 * @type: theme.style.editor
 * name: shape
 * title: layout_shape
 */
import { FormBuilder, FormElementShape } from '@metafox/form';
import React from 'react';

const formSchema: FormElementShape = {
  component: 'Form',
  title: 'Basic',
  submitOnValueChanged: true,
  elements: {
    fonts: {
      component: 'Container',
      sx: { p: 2 },
      elements: {
        borderRadius: {
          name: 'shape.borderRadius',
          component: 'Text',
          type: 'number',
          label: 'borderRadius',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    },
    blockShadow: {
      component: 'Container',
      sx: { p: 2 },
      elements: {
        borderRadius: {
          name: 'blockShadow',
          component: 'Text',
          label: 'blockShadow',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        }
      }
    }
  }
};

export default function FontSnippet(props: any) {
  return <FormBuilder noHeader formSchema={formSchema} {...props} />;
}
