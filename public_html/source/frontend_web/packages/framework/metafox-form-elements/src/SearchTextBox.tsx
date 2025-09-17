/**
 * @type: formElement
 * name: form.element.SearchTextBox
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, isString } from 'lodash';
import Description from './Description';
import ErrorTooltip from './ErrorTooltip';
import Warning from './Warning';
import React from 'react';

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

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const SearchTextBox = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');

  const [valueInput, setValueInput] = React.useState(field.value);

  const {
    label,
    disabled,
    autoComplete,
    placeholder,
    noFeedback,
    variant,
    margin,
    fullWidth,
    type = 'text',
    rows,
    size,
    required,
    multiline,
    description,
    autoFocus,
    readOnly,
    maxLength,
    hasFormLabel = false,
    showErrorTooltip = false,
    sx,
    sxFieldWrapper,
    hasFormOrder = false,
    order,
    styleGroup = 'normal',
    preventScrolling = false,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    warning,
    alwayShowDescription = true,
    defaultValue,
    component, // fix React warning.
    requiredWhen,
    enabledWhen,
    ...rest
  } = config;

  let haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  if (autoComplete && autoFocus) {
    haveError = haveError && field.value !== undefined;
  }

  React.useEffect(() => {
    if (
      field.value === undefined &&
      formik.submitCount &&
      autoComplete &&
      autoFocus
    ) {
      setValue('');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formik.submitCount]);

  const handleBlur = e => {
    isString(valueInput) && setValue(valueInput.trim());
    field.onBlur(e);
  };

  const showError = !showErrorTooltip && haveError && meta.error;

  let helperText = null;

  if (description) {
    helperText = <Description text={description} />;
  }

  if (showError) {
    helperText = meta.error;
  }

  if (description && alwayShowDescription && showError) {
    helperText = (
      <>
        <Description text={description} />
        <Description text={meta.error} error />
      </>
    );
  }

  if (noFeedback) {
    helperText = null;
  }

  const orderLabel = hasFormOrder && order ? `${order}. ` : null;

  const rangeNumber =
    type === 'number' ? { min: minNumber, max: maxNumber } : {};

  const handleChangeText = (evt: any) => {
    const value = evt.target.value;

    setValueInput(value);
  };

  const handleKeyDown = (evt: any) => {
    if (evt.keyCode === 13) {
      // enter
      evt.preventDefault();
      setValue(valueInput);
    }
  };

  React.useEffect(() => {
    setValueInput(field.value || '');
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
      {hasFormLabel && (
        <Title sx={{ mb: 2 }} variant={variant} styleGroup={styleGroup}>
          {orderLabel}
          {label}
        </Title>
      )}
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <Text
          {...rest}
          // careful change this value, can make shrink overlap when autofill data
          // dont use (field.value ?? '') => it make issue on some case(ex: browser autofill data)
          value={valueInput}
          name={field.name}
          onChange={handleChangeText}
          onKeyDown={handleKeyDown}
          onWheel={e => preventScrolling && e.target?.blur()}
          error={haveError}
          multiline={!!(rows && multiline)}
          // flagDisabled={disabled || forceDisabled || formik.isSubmitting}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          required={required || forceRequired}
          size={size}
          onBlur={handleBlur}
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
            'data-testid': camelCase(`input ${name}`)
          }}
          // careful change this InputLabelProps, can make shrink overlap when autofill data
          InputLabelProps={{ shrink: field.value || undefined }}
          label={!hasFormLabel ? label : undefined}
          rows={rows}
          placeholder={placeholder ?? label}
          type={type}
          defaultValue={field.value ?? defaultValue}
          helperText={helperText}
          variant={variant}
          sx={sx}
        />
      </ErrorTooltip>
      <Warning warning={warning} />
    </FormControl>
  );
};

export default SearchTextBox;
