/**
 * @type: theme.style.editor
 * name: typo
 * title: layout_typography
 */
import { useTheme } from '@emotion/react';
import { FormBuilder, FormElementShape } from '@metafox/form';
import { Theme } from '@mui/material';
import { get } from 'lodash';
import React from 'react';

const typoContainer = (name: string, label: string, theme: Theme) => {
  return {
    component: 'AccordionContainer',
    label,
    elements: {
      fontSize: {
        name: `typography.${name}.fontSize`,
        component: 'Text',
        variant: 'standard',
        label: 'Font Size',
        size: 'medium',
        margin: 'dense',
        defaultValue: get(theme, `typography.${name}.fontSize`)
      },
      fontWeight: {
        name: `typography.${name}.fontWeight`,
        component: 'Text',
        variant: 'standard',
        label: 'Font Weight',
        size: 'medium',
        margin: 'dense',
        defaultValue: get(theme, `typography.${name}.fontWeight`)
      },
      lineHeight: {
        name: `typography.${name}.lineHeight`,
        component: 'Text',
        variant: 'standard',
        label: 'Line Height',
        size: 'medium',
        margin: 'dense',
        defaultValue: get(theme, `typography.${name}.lineHeight`)
      },
      letterSpacing: {
        name: `typography.${name}.letterSpacing`,
        component: 'Text',
        variant: 'standard',

        label: 'Letter Spacing',
        size: 'medium',
        margin: 'dense',
        defaultValue: get(theme, `typography.${name}.letterSpacing`, 0)
      }
    }
  };
};

const typoContainerGroup = theme => {
  return [
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'subtitle1',
    'subtitle2',
    'body1',
    'body2',
    'caption',
    'overline'
  ].reduce((acc, typo) => {
    acc[typo] = typoContainer(typo, typo, theme);

    return acc;
  }, {});
};

const createSchema = (theme: Theme): FormElementShape => ({
  component: 'Form',
  submitOnValueChanged: true,
  elements: {
    ...typoContainerGroup(theme)
  }
});

export default function Palette(props: any) {
  const theme = useTheme();
  const schema = createSchema(theme);

  return <FormBuilder noHeader formSchema={schema} {...props} />;
}
