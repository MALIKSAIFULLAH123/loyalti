/**
 * @type: formElement
 * name: form.element.Withdrawal
 * chunkName: formExtras
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
import { camelCase, get, isEmpty, isString, toNumber } from 'lodash';
import Description from '@metafox/form-elements/Description';
import ErrorTooltip from '@metafox/form-elements/ErrorTooltip';
import Warning from '@metafox/form-elements/Warning';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { formatNumberSeparator } from '@metafox/form-elements/utils';
import HtmlViewer from '@metafox/html-viewer';

const formatPriceCurrency = ({ price, pattern }) => {
  let result: any = 0;

  if (!price || isEmpty(pattern)) return 0;

  result = pattern.replace(/{3\}/gi, `<b>${price}</b>`);

  return result;
};

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& p, input::placeholder': {
    color: theme.palette.text.hint
  }
}));

const WithdrawalField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const [field, meta, { setValue }] = useField(name ?? 'TextField');
  const [fieldRelated] = useField(config?.relatedFieldName);
  const { relatedFieldConfigs } = config;
  const configRelated = fieldRelated
    ? get(relatedFieldConfigs, fieldRelated?.value)
    : {};
  const finalConfig = { ...config, ...configRelated };
  const {
    label,
    disabled,
    autoComplete,
    placeholder,
    variant,
    margin,
    fullWidth,
    type = 'number',
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
    preventScrolling = false,
    startAdornment,
    endAdornment,
    min,
    max,
    warning,
    defaultValue,
    inputLabelProps = {},
    className,
    balanceDescription,
    amountCalculation,
    ...rest
  } = finalConfig;
  const {
    percentageFee,
    currencyFormattedPattern,
    totalPhrase,
    description: descriptionAmount
  } = amountCalculation || {};
  const { i18n } = useGlobal();

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

  const rangeNumber = type === 'number' ? { min, max } : {};

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
    >
      {balanceDescription ? (
        <Typography mb={2}>
          <HtmlViewer html={balanceDescription || ''} />
        </Typography>
      ) : null}
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <Text
          {...rest}
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
          helperText={
            <>
              {description ? <Description text={description} /> : null}

              {showError ? <Description text={meta.error} error /> : null}
            </>
          }
          variant={variant}
          sx={sx}
          className={className}
        />
      </ErrorTooltip>
      {descriptionAmount ? (
        <Typography mt={1}>
          <HtmlViewer html={descriptionAmount || ''} />
        </Typography>
      ) : null}
      {totalPhrase ? (
        <Typography mt={1}>
          <div
            dangerouslySetInnerHTML={{
              __html: i18n.formatMessage(
                { id: totalPhrase },
                {
                  value: formatPriceCurrency({
                    price: formatNumberSeparator({
                      number: toNumber(field.value || 0) * (1 - percentageFee),
                      thousand_separator:
                        currencyFormattedPattern?.thousand_separator,
                      precision: currencyFormattedPattern?.precision,
                      decimal_separator:
                        currencyFormattedPattern?.decimal_separator
                    }),
                    pattern: currencyFormattedPattern?.pattern
                  })
                }
              )
            }}
          />
        </Typography>
      ) : null}
      <Warning warning={warning} />
    </FormControl>
  );
};

export default WithdrawalField;
