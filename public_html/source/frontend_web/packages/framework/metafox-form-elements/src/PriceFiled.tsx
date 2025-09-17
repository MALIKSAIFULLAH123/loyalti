/**
 * @type: formElement
 * name: form.element.Price
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps,
  InputLabel,
  Stack
} from '@mui/material';
import { useField, useFormikContext } from 'formik';
import { camelCase } from 'lodash';
import Description from './Description';
import React from 'react';
import { detect } from 'detect-browser';
import { InputNotched } from '@metafox/ui';
import { when } from '@metafox/utils';
import ErrorMessage from './ErrorMessage';

const Label = styled(InputLabel, {
  name: 'Label'
})<{ haveError?: boolean }>(({ theme, haveError }) => ({
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const PriceFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');
  const { values } = useFormikContext();

  const {
    label,
    disabled,
    autoComplete,
    variant = 'outlined',
    margin = 'normal',
    fullWidth,
    type: typeProp = 'text',
    size,
    description,
    autoFocus,
    readOnly,
    maxLength,
    sxFieldWrapper,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    options = []
  } = config;
  const { name: browserName } = detect() || {};
  let type = typeProp;

  // TODO: we should improve on future
  if (
    browserName &&
    ['safari', 'firefox'].includes(browserName) &&
    type === 'number'
  ) {
    type = 'text';
  }

  const rangeNumber =
    type === 'number' ? { min: minNumber, max: maxNumber } : {};

  const haveErr = !!(meta.error && (meta.touched || formik.submitCount));

  const onChangeTextField = (e, index) => {
    setValue({
      ...field.value,
      [options[index].key]: e.target.value
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
      disabled={disabled}
    >
      <Label
        haveError={haveErr}
        required={forceRequired || options.some(item => item?.required)}
        variant="outlined"
        shrink="true"
      >
        {label}
      </Label>
      {description && <Description text={description} sx={{ ml: 2, mt: 2 }} />}
      <Stack m={2} spacing={2}>
        {options.map((item, index) => (
          <Text
            key={index.toString()}
            value={field.value ? field.value[item?.key] : ''}
            name={field.name}
            onChange={e => onChangeTextField(e, index)}
            disabled={item?.disabled || forceDisabled || formik.isSubmitting}
            required={
              item?.required ||
              (item?.requiredWhen ? when(values, item?.requiredWhen) : false)
            }
            size={size}
            onBlur={field.onBlur}
            data-testid={camelCase(`field ${name} ${item.key}`)}
            InputProps={{
              startAdornment: startAdornment ? (
                <InputAdornment position="start">
                  {startAdornment}
                </InputAdornment>
              ) : null,
              endAdornment: endAdornment ? (
                <InputAdornment position="end">{endAdornment}</InputAdornment>
              ) : null
            }}
            inputProps={{
              ...rangeNumber,
              readOnly,
              autoFocus,
              autoComplete,
              maxLength,
              'data-testid': camelCase(`input ${name} ${item.key}`)
            }}
            label={item?.label}
            placeholder={item?.placeholder ?? item?.label}
            type={type}
            helperText={
              item?.description && <Description text={item?.description} />
            }
          />
        ))}
        {meta.error && haveErr && <ErrorMessage error={meta.error} />}
      </Stack>
      <InputNotched haveError={haveErr} children={label} variant="outlined" />
    </FormControl>
  );
};

export default PriceFormField;
