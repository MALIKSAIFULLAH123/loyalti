/**
 * @type: formElement
 * name: form.element.SponsorCalculatorCost
 * chunkName: formAdvertise
 */

import { FormFieldProps } from '@metafox/form';
import {
  Box,
  FormControl,
  InputAdornment,
  styled,
  TextField,
  TextFieldProps,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, isEmpty, isString } from 'lodash';
import React from 'react';
import { useGlobal } from '@metafox/framework';
import { formatNumberSeparator } from '@metafox/form-elements/utils';
import { Description } from '@metafox/form-elements';
import ErrorTooltip from '@metafox/form-elements/ErrorTooltip';
import Warning from '@metafox/form-elements/Warning';

const TextFieldStyled = styled(TextField)(({ theme }) => ({
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
}));

const TotalStyled = styled(Box, { slot: 'total' })(({ theme }) => ({
  display: 'flex',
  marginTop: theme.spacing(3)
}));

const LabelTotalStyled = styled(Typography, { slot: 'label' })(({ theme }) => ({
  marginRight: theme.spacing(0.5),
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(18)
}));

const TotalValue = styled(Typography, { slot: 'value' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(18)
}));

const calculatorFunction = (price, initialPrice, initialUnit) => {
  try {
    return (price * initialPrice) / initialUnit;
  } catch (err) {
    return 0;
  }
};

const formatPriceCurrency = ({ price, pricePattern }) => {
  let result: any = 0;

  if (!price || isEmpty(pricePattern?.pattern)) return 0;

  result = pricePattern.pattern.replace(/{3\}/gi, `<b>${price}</b>`);

  return result;
};

const SponsorCalculatorCost = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const { i18n } = useGlobal();
  const [field, meta, { setValue }] = useField(name ?? 'TextField');

  const {
    label = '',
    disabled,
    autoComplete,
    placeholder,
    noFeedback,
    variant,
    margin,
    fullWidth,
    type = 'number',
    size,
    required,
    multiline,
    description,
    autoFocus,
    readOnly,
    showErrorTooltip = false,
    sx,
    sxFieldWrapper,
    preventScrolling = false,
    startAdornment,
    endAdornment,
    minNumber,
    maxNumber,
    warning,
    hoverState,
    returnKeyType,
    alwayShowDescription = true,
    defaultValue,
    component, // fix React warning.
    testid,
    showWhen,
    requiredWhen,
    enabledWhen,
    totalNameLabel = 'total_cost',
    initialUnit = 1000,
    pricePattern,
    initialPrice,
    inputLabelProps = {},
    ...rest
  } = config;
  const isNumberInput = type === 'number';

  const [priceView, setPriceView] = React.useState<any>(0);

  let haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  if (autoComplete && autoFocus) {
    haveError = haveError && field.value !== undefined;
  }

  React.useEffect(() => {
    if (field.value === undefined && formik.submitCount) {
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

  const rangeNumber = { min: minNumber, max: maxNumber };

  const priceCalculator = React.useMemo(() => {
    return calculatorFunction(field.value, initialPrice, initialUnit);
  }, [field.value, initialPrice, initialUnit]);

  React.useEffect(() => {
    setPriceView(
      formatNumberSeparator({
        number: priceCalculator,
        thousand_separator: pricePattern?.thousand_separator,
        precision: pricePattern?.precision,
        decimal_separator: pricePattern?.decimal_separator
      })
    );

    return () => {
      setPriceView(0);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [priceCalculator]);

  return (
    <FormControl
      margin={margin ?? 'normal'}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
    >
      <ErrorTooltip name={field.name} showErrorTooltip={showErrorTooltip}>
        <TextFieldStyled
          {...rest}
          value={autoComplete && autoFocus ? undefined : field.value ?? ''}
          name={field.name}
          onChange={field.onChange}
          onWheel={e => preventScrolling && e.target?.blur()}
          error={haveError}
          multiline={false}
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
            'data-testid': camelCase(`input ${name}`)
          }}
          InputLabelProps={{
            shrink: field.value || (isNumberInput && haveError) || undefined,
            ...Object.assign({}, inputLabelProps)
          }}
          label={label}
          placeholder={placeholder ?? label}
          type={type}
          defaultValue={field.value ?? defaultValue}
          helperText={helperText}
          variant={variant}
          sx={sx}
        />

        <TotalStyled>
          {totalNameLabel ? (
            <LabelTotalStyled>
              {i18n.formatMessage({ id: totalNameLabel })}:
            </LabelTotalStyled>
          ) : null}
          <TotalValue>
            {priceView !== 0 ? (
              <span
                dangerouslySetInnerHTML={{
                  __html: formatPriceCurrency({
                    price: priceView,
                    pricePattern
                  })
                }}
              />
            ) : (
              0
            )}
          </TotalValue>
        </TotalStyled>
      </ErrorTooltip>
      <Warning warning={warning} />
    </FormControl>
  );
};

export default SponsorCalculatorCost;
