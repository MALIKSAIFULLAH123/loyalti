/**
 * @type: formElement
 * name: form.element.InviteCode
 * chunkName: formInvite
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
import { useField, useFormikContext } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import Visibility from '@mui/icons-material/Visibility';
import VisibilityOff from '@mui/icons-material/VisibilityOff';
import { Description } from '@metafox/form-elements';

const TextFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field] = useField(name ?? 'TextField');
  const { i18n, toastBackend, dialogBackend, apiClient } = useGlobal();

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
    action: { confirm, url, method } = {},
    hasRefresh,
    ...rest
  } = config;
  const [showText, setShowText] = React.useState(type === 'text');
  const { setValues } = useFormikContext();
  const [disableRefresh, setDisableRefresh] = React.useState(false);

  const handleClickShowText = () => setShowText(show => !show);

  const copyClipBoard = React.useCallback(() => {
    try {
      navigator.clipboard.writeText(field.value);
      toastBackend.success(i18n.formatMessage({ id: 'copied_to_clipboard' }));
    } catch (err) {}
  }, [field.value]);

  const refreshCode = async () => {
    setDisableRefresh(true);

    if (confirm) {
      const ok = await dialogBackend.confirm({
        message: i18n.formatMessage(
          { id: confirm?.message },
          { invite_code: field.value }
        )
      });

      if (!ok) {
        setDisableRefresh(false);

        return;
      }
    }

    apiClient
      .request({ method, url })
      .then(result => result.data?.data)
      .then(data => {
        if (data) {
          setValues(prevValues => ({ ...prevValues, ...data }));
        }
      })
      .finally(() => {
        setDisableRefresh(false);
      });
  };

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
          {hasRefresh && (
            <Button
              sx={{ height: '100%', ml: 1 }}
              variant="contained"
              size={size}
              onClick={refreshCode}
              disabled={disableRefresh}
            >
              {i18n.formatMessage({ id: 'refresh' })}
            </Button>
          )}
        </Box>
      </Box>
      {description ? <Description text={description} /> : null}
    </FormControl>
  );
};

export default TextFormField;
