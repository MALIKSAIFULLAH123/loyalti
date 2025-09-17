/**
 * @type: formElement
 * name: form.element.Text
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps,
  FormLabel,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, isString } from 'lodash';
import Description from './Description';
import ErrorTooltip from './ErrorTooltip';
import Warning from './Warning';
import React from 'react';
import { toFindReplaceSlugify } from './utils';
import { Image } from '@metafox/ui';

const Title = styled(FormLabel, {
  name: 'Title'
})(({ theme }) => ({
  marginBottom: theme.spacing(2)
}));

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const TextDescription = styled(Typography, {
  name: 'TextDescription'
})(({ theme }) => ({
  fontSize: theme.typography.body1.fontSize,
  lineHeight: 1.6,
  color: theme.palette.text.primary,
  paddingBottom: theme.spacing(1)
}));

const ImagePreview = styled(Image, {
  name: 'ImagePreview'
})(({ theme }) => ({
  marginBottom: theme.spacing(2)
}));

const TextFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');

  const {
    label,
    disabled,
    autoComplete,
    placeholder,
    noFeedback,
    variant,
    titleConfig = {},
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
    preventScrolling = false,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    warning,
    hoverState,
    returnKeyType,
    alwayShowDescription = true,
    contextualDescription,
    defaultValue,
    component, // fix React warning.
    testid,
    showWhen,
    requiredWhen,
    enabledWhen,
    findReplace,
    inputLabelProps = {},
    className,
    imageProps,
    ...rest
  } = config;

  const isNumberInput = type === 'number';

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
    isString(field.value) && setValue(field.value.trim());
    field.onBlur(e);
  };

  const showError = !showErrorTooltip && haveError && meta.error;

  let helperText = null;

  if (description) {
    helperText = <Description text={description} />;
  }

  if (showError) {
    helperText = <Description text={meta.error} error />;
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

  const suffixDescription = findReplace
    ? toFindReplaceSlugify(field.value, findReplace?.find, findReplace?.replace)
    : field.value;

  React.useEffect(() => {
    if (findReplace) {
      setValue(suffixDescription);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [findReplace, suffixDescription]);

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
        <Title
          focused={false}
          required={required || forceRequired}
          {...titleConfig}
        >
          {orderLabel}
          {label}
        </Title>
      )}
      {contextualDescription && (
        <TextDescription>
          {`${contextualDescription}${suffixDescription}`}
        </TextDescription>
        // <Description text={`${contextualDescription}${suffixDescription}`} />
      )}
      {imageProps?.src ? <ImagePreview {...imageProps} /> : null}
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <Text
          {...rest}
          // careful change this value, can make shrink overlap when autofill data
          // dont use (field.value ?? '') => it make issue on some case(ex: browser autofill data)
          value={field.value ?? ''}
          name={field.name}
          onChange={field.onChange}
          onKeyDown={
            isNumberInput
              ? evt => {
                  if (
                    evt.ctrlKey ||
                    evt.metaKey ||
                    evt.key === 'ArrowLeft' ||
                    evt.key === 'ArrowRight' ||
                    evt.key === 'Delete' ||
                    evt.key === 'Backspace' ||
                    evt.key === 'Tab' ||
                    evt.key === 'Enter' ||
                    evt.key === '.'
                  ) {
                    return;
                  }

                  if (!/[0-9]/.test(evt.key)) {
                    evt.preventDefault();
                  }
                }
              : undefined
          }
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
          InputLabelProps={{
            shrink: field.value || (isNumberInput && haveError) || undefined,
            ...Object.assign({}, inputLabelProps)
          }}
          label={!hasFormLabel ? label : undefined}
          rows={rows}
          placeholder={placeholder ?? label}
          type={type}
          defaultValue={field.value ?? defaultValue}
          helperText={helperText}
          variant={variant}
          sx={sx}
          className={className}
        />
      </ErrorTooltip>
      <Warning warning={warning} />
    </FormControl>
  );
};

export default TextFormField;
