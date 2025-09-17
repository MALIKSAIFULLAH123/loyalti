/**
 * @type: formElement
 * name: form.element.Description
 * chunkName: formBasic
 */
import {
  FormControl,
  FormHelperText,
  FormLabel,
  Typography
} from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

const DescriptionField = ({ config, name, formik }: FormFieldProps) => {
  const {
    label,
    margin,
    disabled,
    fullWidth,
    hiddenLabel,
    required,
    variant,
    size,
    description,
    descriptionProps = {},
    labelProps = {}
  } = config;

  return (
    <FormControl
      sx={{ display: 'block' }}
      margin={margin ?? 'normal'}
      disabled={disabled}
      fullWidth={fullWidth}
      hiddenLabel={hiddenLabel}
      required={required}
      size={size}
      variant={variant as any}
      data-testid={camelCase(`field ${name}`)}
    >
      {label ? (
        <FormLabel>
          <Typography color={'inherit'} {...labelProps}>
            {label}
          </Typography>
        </FormLabel>
      ) : null}
      {description ? (
        <FormHelperText>
          <Typography color="inherit" {...descriptionProps}>
            {description}
          </Typography>
        </FormHelperText>
      ) : null}
    </FormControl>
  );
};

export default DescriptionField;
