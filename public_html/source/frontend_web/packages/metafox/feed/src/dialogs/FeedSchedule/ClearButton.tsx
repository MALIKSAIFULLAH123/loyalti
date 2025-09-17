/**
 * @type: formElement
 * name: form.element.FeedScheduleClear
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { Button as MuiButton, FormControl } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const FeedScheduleClearButton = ({
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const {
    sxFieldWrapper,
    type = 'button',
    variant,
    disabled,
    color,
    size,
    label,
    className,
    margin,
    fullWidth,
    name,
    schedule_time_key
  } = config;

  const handleClick = React.useCallback(() => {
    formik.setFieldValue(schedule_time_key, undefined);
  }, []);

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        color={color}
        size={size}
        type={type}
        className={className}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
      >
        {label}
      </MuiButton>
    </FormControl>
  );
};

export default FeedScheduleClearButton;
