/**
 * @type: formElement
 * name: form.element.FilterPrice
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import {
  Box,
  Button,
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React, { useState } from 'react';

const Title = styled(Typography, {
  name: 'Title',
  shouldForwardProp: prop => prop !== 'styleGroup'
})<{ styleGroup?: string }>(({ theme, styleGroup }) => ({
  ...(styleGroup === 'question' && {
    color: theme.palette.text.secondary,
    fontWeight: theme.typography.fontWeightBold
  }),
  ...(styleGroup === 'normal' && {})
}));

const TextFieldStyled = styled(TextField, { name: 'boxInput' })(
  ({ theme }) => ({
    '& input[type=number]': {
      MozAppearance: 'textfield'
    },
    '& input[type=number]::-webkit-outer-spin-button': {
      WebkitAppearance: 'none',
      margin: 0
    },
    '& input[type=number]::-webkit-inner-spin-button': {
      WebkitAppearance: 'none',
      margin: 0
    },
    '& input::placeholder': {
      color: theme.palette.text.hint
    }
  })
);

const regex = /^\d*(\.\d{1,2})?$/;

const FilterPriceField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const {
    fromFieldName,
    toFieldName,
    fromFieldLabel,
    toFieldLabel,
    separatorLabel,
    label,
    disabled,
    autoComplete,
    placeholder,
    variant,
    margin,
    fullWidth,
    rows,
    size,
    required,
    autoFocus,
    readOnly,
    maxLength,
    sx,
    component,
    sxFieldWrapper,
    styleGroup = 'normal',
    preventScrolling = false,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    testid,
    submitFieldLabel,
    ...rest
  } = config;

  const [fieldFrom, , { setValue: setValueFrom }] = useField(
    fromFieldName ?? 'price_from'
  );
  const [fieldTo, , { setValue: setValueTo }] = useField(
    toFieldName ?? 'price_to'
  );

  const [tempFrom, setTempFrom] = useState(fieldFrom.value);
  const [tempTo, setTempTo] = useState(fieldTo.value);

  // used for the case of changing the value from the outside
  React.useEffect(() => {
    if (fieldFrom.value === undefined) {
      setTempFrom('');
    }
  }, [fieldFrom.value]);

  React.useEffect(() => {
    if (fieldTo.value === undefined) {
      setTempTo('');
    }
  }, [fieldTo.value]);

  const handleBlurToField = e => {
    fieldTo.onBlur(e);
  };

  const handleBlurFromField = e => {
    fieldFrom.onBlur(e);
  };

  const rangeNumber = { min: minNumber, max: maxNumber };

  const onChangeFrom = e => {
    const value = e.target.value;

    if (value.trim() === '') setTempFrom('');

    if (value.trim() !== '' && regex.test(Number(value))) {
      // support decimal
      if (regex.test(value)) setTempFrom(`${Number(value)}`);
      else setTempFrom(value);
    }
  };

  const onChangeTo = e => {
    const value = e.target.value;

    if (value.trim() === '') setTempTo('');

    if (value.trim() !== '' && regex.test(Number(value))) {
      // support decimal
      if (regex.test(value)) setTempTo(`${Number(value)}`);
      else setTempTo(value);
    }
  };

  const onSubmit = () => {
    if (Number(tempFrom) > Number(tempTo) && tempFrom && tempTo) {
      setValueFrom(tempTo);
      setValueTo(tempFrom);
      setTempFrom(tempTo);
      setTempTo(tempFrom);
    } else {
      setValueFrom(tempFrom || undefined);
      setValueTo(tempTo || undefined);
    }
  };

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={{ mt: '8px' }}
      id={name}
    >
      <Title sx={{ mb: 1 }} variant={variant} styleGroup={styleGroup}>
        {label}
      </Title>
      <Box sx={{ display: 'flex', alignItems: 'center' }}>
        <TextFieldStyled
          {...rest}
          value={tempFrom}
          name={fromFieldLabel}
          onChange={onChangeFrom}
          onWheel={e => preventScrolling && e.target?.blur()}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          required={required || forceRequired}
          size={size}
          onBlur={handleBlurFromField}
          InputProps={{
            startAdornment: startAdornment ? (
              <InputAdornment position="start">{startAdornment}</InputAdornment>
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
            'data-testid': camelCase(`input ${fromFieldLabel}`)
          }}
          label={fromFieldLabel}
          rows={rows}
          placeholder={fromFieldLabel}
          variant={variant}
          sx={sx}
        />
        <Box mx={1}>{separatorLabel}</Box>
        <TextFieldStyled
          {...rest}
          value={tempTo}
          name={toFieldLabel}
          onChange={onChangeTo}
          onWheel={e => preventScrolling && e.target?.blur()}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          required={required || forceRequired}
          size={size}
          onBlur={handleBlurToField}
          InputProps={{
            startAdornment: startAdornment ? (
              <InputAdornment position="start">{startAdornment}</InputAdornment>
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
            'data-testid': camelCase(`input ${toFieldLabel}`)
          }}
          label={toFieldLabel}
          rows={rows}
          placeholder={toFieldLabel}
          variant={variant}
          sx={sx}
        />
      </Box>
      <Button
        variant="contained"
        size="medium"
        sx={{ mt: 1 }}
        onClick={onSubmit}
      >
        {submitFieldLabel}
      </Button>
    </FormControl>
  );
};

export default FilterPriceField;
