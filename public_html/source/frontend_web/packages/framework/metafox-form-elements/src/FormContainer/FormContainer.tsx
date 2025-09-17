/**
 * @type: formElement
 * name: form.element.Form
 * chunkName: formBasic
 */
import { styled } from '@mui/material/styles';
import { Form } from 'formik';
import { camelCase, map } from 'lodash';
import React from 'react';
import { Element, FormFieldProps } from '@metafox/form';

const FormRoot = styled(Form, {
  name: 'Form',
  slot: 'Root',
  shouldForwardProp: (prop: string) => !/horizontal|align/i.test(prop)
})<{ horizontal?: boolean }>(({ theme, horizontal }) => ({
  display: 'flex',
  ...(horizontal
    ? {
        flexDirection: 'row',
        flexWrap: 'wrap',
        '& > div': {
          minHeight: theme.spacing(5),
          paddingRight: theme.spacing(1)
        }
      }
    : {
        flexDirection: 'column',
        flex: 1,
        width: '100%'
      })
}));

const FormContainer = ({ config }: FormFieldProps) => {
  const {
    elements,
    variant = 'vertical',
    role = 'form',
    title,
    sx,
    testid
  } = config;

  return (
    <FormRoot
      horizontal={variant === 'horizontal'}
      data-testid={camelCase(testid ?? 'form')}
      role={role}
      sx={sx}
      aria-label={title}
      id={testid ?? 'form'}
    >
      {map(elements, (config, key) => (
        <Element key={key.toString()} config={config} />
      ))}
    </FormRoot>
  );
};

export default FormContainer;
