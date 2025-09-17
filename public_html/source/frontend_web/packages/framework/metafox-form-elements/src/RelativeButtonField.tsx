/**
 * @type: formElement
 * name: form.element.RelativeButton
 * chunkName: formBasic
 */
import { FormControl, useMediaQuery, useTheme } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';

function RelativeButton({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    type = 'button',
    disabled,
    controlProps = {},
    color = 'primary',
    margin,
    flexWidth,
    label,
    size,
    fullWidth = false,
    className,
    variant,
    action
  } = config;
  const { dispatch } = useGlobal();
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  const handleClick = () => {
    dispatch({ type: action, payload: formik.values });
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      {...controlProps}
      sx={{
        minWidth: !isSmallScreen && flexWidth ? '275px' : 'auto'
      }}
      data-testid={camelCase(`field ${name}`)}
    >
      <MuiButton
        fullWidth={fullWidth}
        variant={variant as any}
        role="button"
        id={camelCase(`button ${name}`)}
        color={color}
        size={size}
        type={type}
        className={className}
        disabled={disabled || formik.isSubmitting || forceDisabled}
        data-testid={camelCase(`button ${name}`)}
        onClick={handleClick}
      >
        {label}
      </MuiButton>
    </FormControl>
  );
}
export default RelativeButton;
