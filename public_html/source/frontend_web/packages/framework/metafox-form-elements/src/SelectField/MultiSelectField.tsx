import { OptionItemShape as ItemShape } from '@metafox/framework';
import {
  Autocomplete,
  FormControl,
  FormHelperText,
  TextField
} from '@mui/material';
import { useField } from 'formik';
import { isArray } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

type MultiSelectFieldProps = FormFieldProps & {
  optionMap: Record<string, ItemShape>;
  renderOption: any;
  getOptionLabel: any;
  isOptionEqualToValue: any;
};

export default function MultiSelectField({
  config,
  name,
  optionMap,
  renderOption,
  getOptionLabel,
  isOptionEqualToValue
}: MultiSelectFieldProps) {
  const [field, , { setValue }] = useField(name ?? 'SelectField');
  const {
    label,
    margin,
    disabled,
    autoComplete = 'off',
    options = [],
    fullWidth,
    hiddenLabel,
    required,
    variant,
    size,
    style,
    description,
    fieldValue = 'value',
    openOnFocus = true
  } = config;

  const handleChange = React.useCallback(
    newValue => {
      if (isArray(newValue)) {
        setValue(newValue.map(option => option[fieldValue]));
      } else {
        setValue([]);
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  // validate use value
  const optionValue: ItemShape[] = React.useMemo(() => {
    if (isArray(field.value)) {
      return field.value.map(value => optionMap[value]);
    } else {
      return [];
    }
  }, [field.value, optionMap]);

  if (!options.length) return null;

  const renderInput = params => {
    return (
      <TextField
        {...params}
        label={label}
        variant={variant}
        size={size}
        required={required}
        inputProps={{
          ...params.inputProps,
          autoComplete
        }}
      />
    );
  };

  return (
    <FormControl
      margin={margin ?? 'normal'}
      disabled={disabled}
      fullWidth={fullWidth}
      hiddenLabel={hiddenLabel}
      required={required}
      style={style}
      size={size}
      variant={variant as any}
    >
      <Autocomplete<ItemShape, true>
        openOnFocus={openOnFocus}
        value={optionValue}
        onChange={(_, newValue) => handleChange(newValue)}
        onBlur={field.onBlur}
        id={`select-${name}`}
        isOptionEqualToValue={isOptionEqualToValue}
        options={options}
        multiple
        getOptionLabel={getOptionLabel}
        autoHighlight
        renderInput={renderInput}
        renderOption={renderOption}
      />
      {description ? <FormHelperText children={description} /> : null}
    </FormControl>
  );
}
