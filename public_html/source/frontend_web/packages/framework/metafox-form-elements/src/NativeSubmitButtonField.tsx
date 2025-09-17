/**
 * @type: formElement
 * name: form.element.NativeSubmit
 * chunkName: formBasic
 */
import MuiButton from '@mui/lab/LoadingButton';
import { FormControl, useMediaQuery } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import { useFormikContext } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

function SubmitButtonField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    disabled,
    color = 'primary',
    size,
    type,
    margin,
    flexWidth,
    label,
    fullWidth = false,
    className,
    variant = 'contained',
    disableWhenClean,
    sxFieldWrapper
  } = config;
  const { dirty } = useFormikContext();

  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      sx={{
        minWidth: !isSmallScreen && flexWidth ? '275px' : 'auto',
        ...sxFieldWrapper
      }}
      data-testid={camelCase(`field ${name}`)}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        color={color}
        size={isSmallScreen ? 'medium' : size}
        loading={formik.isSubmitting}
        className={className}
        type={type}
        role="button"
        disabled={
          disabled ||
          forceDisabled ||
          (disableWhenClean && !dirty) ||
          formik.isSubmitting
        }
        data-testid={camelCase(`button ${name}`)}
      >
        {label}
      </MuiButton>
    </FormControl>
  );
}

export default SubmitButtonField;
