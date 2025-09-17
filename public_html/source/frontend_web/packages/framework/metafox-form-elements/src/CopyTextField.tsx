/**
 * @type: formElement
 * name: form.element.CopyText
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import {
  FormControl,
  InputAdornment,
  TextField,
  TextFieldProps,
  Button,
  Box,
  IconButton
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import Visibility from '@mui/icons-material/Visibility';
import VisibilityOff from '@mui/icons-material/VisibilityOff';
import Description from './Description';

const TextFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field] = useField(name ?? 'TextField');
  const { i18n, toastBackend } = useGlobal();

  const {
    label,
    disabled,
    autoComplete,
    placeholder,
    variant,
    margin,
    fullWidth,
    type = 'text',
    size,
    hasFormLabel = false,
    sx,
    sxFieldWrapper,
    startAdornment,
    endAdornment,
    defaultValue,
    component,
    testid,
    description,
    ...rest
  } = config;
  const [showText, setShowText] = React.useState(type === 'text');

  const handleClickShowText = () => setShowText(show => !show);

  const copyClipBoard = React.useCallback(() => {
    try {
      navigator.clipboard.writeText(field.value);
      toastBackend.success(i18n.formatMessage({ id: 'copied_to_clipboard' }));
    } catch (err) {}
  }, [field.value]);

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
    >
      <Box sx={{ display: 'flex' }}>
        <Box sx={{ flex: 1, minWidth: 0 }}>
          <TextField
            {...rest}
            value={field.value}
            name={field.name}
            onChange={field.onChange}
            disabled={disabled}
            size={size}
            InputProps={{
              startAdornment: startAdornment ? (
                <InputAdornment position="start">
                  {startAdornment}
                </InputAdornment>
              ) : null,
              endAdornment: endAdornment ? (
                <InputAdornment position="end">{endAdornment}</InputAdornment>
              ) : (
                <InputAdornment position="end">
                  <IconButton
                    aria-label="toggle password visibility"
                    onClick={handleClickShowText}
                    edge="end"
                  >
                    {showText ? <VisibilityOff /> : <Visibility />}
                  </IconButton>
                </InputAdornment>
              )
            }}
            inputProps={{
              readOnly: true,
              'data-testid': camelCase(`input ${name}`)
            }}
            label={!hasFormLabel ? label : undefined}
            placeholder={placeholder ?? label}
            type={showText ? 'text' : 'password'}
            defaultValue={field.value ?? defaultValue}
            variant={variant}
            fullWidth
            sx={sx}
          />
        </Box>
        <Box ml={1}>
          <Button
            sx={{ height: '100%' }}
            variant="outlined"
            size={size}
            onClick={copyClipBoard}
          >
            {i18n.formatMessage({ id: 'copy' })}
          </Button>
        </Box>
      </Box>
      {description ? <Description text={description} /> : null}
    </FormControl>
  );
};

export default TextFormField;
