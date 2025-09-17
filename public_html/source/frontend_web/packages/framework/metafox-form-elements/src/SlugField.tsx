/**
/**
 * @type: formElement
 * name: form.element.Slug
 */
import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  InputAdornment,
  styled,
  TextField as MuiTextField,
  TextFieldProps,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, get, isNil, isObject, isString } from 'lodash';
import React from 'react';
import Description from './Description';
import ErrorTooltip from './ErrorTooltip';
import { toSlugifyLowerNonAccent } from './utils';
import Warning from './Warning';

const TextFieldStyled = styled(MuiTextField, {
  shouldForwardProp: props => props !== 'flagDisabled'
})<TextFieldProps & { flagDisabled?: boolean }>(({ theme, flagDisabled }) => ({
  ...(flagDisabled && {
    '& .MuiOutlinedInput-root': {
      '& > fieldset': {
        borderColor:
          theme.palette.mode === 'light'
            ? 'rgba(0, 0, 0, 0.23) !important'
            : 'rgba(255, 255, 255, 0.23) !important'
      }
    }
  })
}));

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

const TextFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');

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
    contextualDescription,
    mappingField,
    separator = '-'
  } = config;

  const init = React.useRef(false);

  // fix: A component is changing an uncontrolled input
  if (isNil(field.value)) {
    field.value = config.defaultValue ?? '';
  }

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  const handleBlur = e => {
    isString(field.value) && setValue(field.value.trim());
    field.onBlur(e);
  };

  const valueMapField = get(formik.values, mappingField);

  React.useEffect(() => {
    if (
      init.current &&
      mappingField &&
      isObject(formik.values) &&
      valueMapField &&
      isString(valueMapField)
    ) {
      const data = valueMapField;
      const dataConvert = toSlugifyLowerNonAccent(data, separator)?.substring(
        0,
        maxLength
      );

      setValue(dataConvert);
    }

    init.current = true;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mappingField, valueMapField]);

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

  return (
    <FormControl
      margin={margin}
      variant={variant as any}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
    >
      {hasFormLabel && (
        <Title sx={{ mb: 2 }} variant={variant as any} styleGroup={styleGroup}>
          {orderLabel}
          {label}
        </Title>
      )}
      {contextualDescription && (
        <Description text={`${contextualDescription}${field.value}`} />
      )}
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <TextFieldStyled
          {...field}
          onWheel={e => preventScrolling && e.target?.blur()}
          error={haveError}
          multiline={!!(rows && multiline)}
          flagDisabled={disabled || forceDisabled || formik.isSubmitting}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          required={required || forceRequired}
          size={size}
          onBlur={handleBlur}
          autoFocus={autoFocus}
          InputProps={{
            autoComplete,
            readOnly,
            startAdornment: startAdornment ? (
              <InputAdornment position="start">{startAdornment}</InputAdornment>
            ) : null,
            endAdornment: endAdornment ? (
              <InputAdornment position="end">{endAdornment}</InputAdornment>
            ) : null
          }}
          inputProps={{
            ...rangeNumber,
            maxLength,
            'data-testid': camelCase(`input ${name}`)
          }}
          label={!hasFormLabel ? label : undefined}
          rows={rows}
          placeholder={placeholder}
          type={type}
          helperText={helperText}
          sx={sx}
        />
      </ErrorTooltip>
      <Warning warning={warning} />
    </FormControl>
  );
};

export default TextFormField;
