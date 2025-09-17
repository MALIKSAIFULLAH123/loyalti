/**
 * @type: formElement
 * name: form.element.Button
 * chunkName: formBasic
 */
import { FormControl, useMediaQuery, useTheme } from '@mui/material';
import MuiButton from '@mui/material/Button';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

function ButtonField({
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
    variant
  } = config;
  const [{ value }, , { setValue }] = useField(name ?? 'ButtonField');
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  React.useEffect(() => {
    if (value) {
      formik.submitForm().finally(() => {
        setValue(undefined);
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  const handleClick = () => {
    if (config.value && name) {
      setValue(config.value);
    } else {
      formik.submitForm();
    }
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
export default ButtonField;
