/**
 * @type: formElement
 * name: form.element.Password
 * chunkName: formBasic
 */
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React, { createElement } from 'react';
import { FormFieldProps } from '@metafox/form';
import ErrorTooltip from './ErrorTooltip';
import { IconButton, InputAdornment, TextField, styled } from '@mui/material';
import VisibilityOffIcon from '@mui/icons-material/VisibilityOff';
import VisibilityIcon from '@mui/icons-material/Visibility';
import Description from './Description';

const MAX_LENGTH_DEFAULT = 255;

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const PasswordField = ({
  config,
  disabled: forceDisabled,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setTouched }] = useField(name ?? 'PasswordField');
  const [show, setShow] = React.useState(false);
  const {
    label,
    placeholder,
    disabled,
    readOnly,
    autoComplete = 'current-password',
    margin,
    fullWidth,
    size,
    required,
    description,
    autoFocus,
    maxLength = MAX_LENGTH_DEFAULT,
    showErrorTooltip = false,
    className
  } = config;

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const handleClickShowPassword = React.useCallback(() => {
    setShow(show => !show);
  }, []);

  const onBlur = e => {
    field.onBlur(e);
    setTouched(true);
  };

  const textField = createElement(Text, {
    name: field.name,
    onChange: field.onChange,
    disabled: disabled || forceDisabled || formik.isSubmitting,
    onBlur,
    required,
    label,
    autoFocus,
    'data-testid': camelCase(`field ${name}`),
    inputProps: {
      'data-testid': camelCase(`input ${name}`),
      maxLength,
      readOnly,
      type: show ? 'text' : 'password'
    },
    autoComplete,
    size,
    placeholder,
    margin,
    error: haveError,
    defaultValue: field.value ?? '',
    fullWidth,
    className,
    helperText:
      !showErrorTooltip && haveError ? (
        meta.error
      ) : description ? (
        <Description text={description} />
      ) : null,

    InputProps: {
      endAdornment: (
        <InputAdornment position="end">
          <IconButton
            aria-label="toggle password visibility"
            onClick={handleClickShowPassword}
            edge="end"
          >
            {show ? <VisibilityIcon /> : <VisibilityOffIcon />}
          </IconButton>
        </InputAdornment>
      )
    }
  });

  return (
    <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
      {textField}
    </ErrorTooltip>
  );
};

export default PasswordField;
