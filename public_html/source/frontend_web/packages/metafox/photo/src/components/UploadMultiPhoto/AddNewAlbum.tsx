/**
 * @type: formElement
 * name: form.element.AddNewAlbum
 */
import { FormFieldProps } from '@metafox/form/types';
import { FormControl } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';

function AddNewAlbumField({ config, name, formik }: FormFieldProps) {
  const [field, , { setValue }] = useField(name ?? 'addNewAlbum');
  const {
    type = 'button',
    disabled,
    controlProps = {},
    color = 'primary',
    margin,
    label,
    size,
    fullWidth = false,
    className,
    variant,
    cancelLabel
  } = config;

  const handleClick = () => {
    setValue(field.value ? 0 : 1);
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      {...controlProps}
      data-testid={camelCase(`field ${name}`)}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        color={color}
        size={size}
        type={type}
        className={className}
        disabled={disabled ?? formik.isSubmitting}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
      >
        {field.value ? label : cancelLabel}
      </MuiButton>
    </FormControl>
  );
}

export default AddNewAlbumField;
