/**
 * @type: theme.style.editor
 * name: fonts
 * title: layout_fonts
 */
import { FormBuilder, FormElementShape } from '@metafox/form';
import React from 'react';

const fontWeightOptions = [100, 200, 300, 400, 500, 600, 700, 800, 900].map(
  x => {
    return {
      label: x.toString(),
      value: x
    };
  }
);

const formSchema: FormElementShape = {
  component: 'Form',
  title: 'Basic',
  submitOnValueChanged: true,
  elements: {
    fonts: {
      component: 'Container',
      sx: { p: 2 },
      elements: {
        fontWeightLight: {
          name: 'typography.fontWeightLight',
          label: 'fontWeightLight',
          component: 'Dropdown',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true,
          options: fontWeightOptions
        },
        fontWeightRegular: {
          name: 'typography.fontWeightRegular',
          label: 'fontWeightRegular',
          component: 'Dropdown',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true,
          options: fontWeightOptions
        },
        fontWeightMedium: {
          name: 'typography.fontWeightMedium',
          label: 'fontWeightMedium',
          component: 'Dropdown',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true,
          options: fontWeightOptions
        },
        fontWeightBold: {
          name: 'typography.fontWeightBold',
          label: 'fontWeightBold',
          component: 'Dropdown',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true,
          options: fontWeightOptions
        },
        htmlFontSize: {
          name: 'typography.htmlFontSize',
          component: 'Text',
          label: 'htmlFontSize',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        fontSize: {
          name: 'typography.fontSize',
          component: 'Text',
          label: 'fontSize',
          variant: 'standard',
          required: true,
          margin: 'dense',
          fullWidth: true
        },
        fontFamily: {
          name: 'fontFamily',
          component: 'Text',
          label: 'Font Family',
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
