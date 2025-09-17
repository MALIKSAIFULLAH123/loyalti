/**
 * @type: formElement
 * name: form.element.RadioGroup
 * chunkName: formElement
 */
import { FormFieldProps } from '@metafox/form';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  FormControl,
  FormControlLabel,
  FormLabel,
  Radio,
  RadioGroup,
  styled,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import ErrorMessage from './ErrorMessage';
import Label from './Label';

const FormLabelStyled = styled(FormLabel, {
  name: 'Title'
})(({ theme }) => ({
  marginBottom: theme.spacing(1)
}));

const LabelStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const RadioStyled = styled(Radio)(({ theme }) => ({
  height: 42,
  width: 42
}));

const DescriptionStyled = styled(Typography)(({ theme }) => ({
  marginLeft: theme.spacing(3.75)
}));

const RadioGroupField = ({
  config,
  name,
  disabled: forceDisabled,
  required: forceRequired,
  formik
}: FormFieldProps) => {
  const [field, meta] = useField(name ?? 'RadioGroupField');
  const {
    options,
    label,
    margin = 'normal',
    variant,
    fullWidth,
    disabled,
    labelPlacement,
    inline,
    size,
    required,
    hasFormOrder = false,
    order,
    description,
    titleConfig = {},
    descriptionConfig,
    descriptionPlacement = 'top'
  } = config;

  const style = inline ? { flexDirection: 'row' } : undefined;

  const orderLabel = hasFormOrder && order ? `${order}. ` : null;

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  return (
    <FormControl
      component="fieldset"
      margin={margin}
      variant={variant as any}
      disabled={disabled || forceDisabled || formik.isSubmitting}
      fullWidth={fullWidth}
      size={size}
      data-testid={camelCase(`field ${name}`)}
      required={required}
    >
      {label || orderLabel ? (
        <FormLabelStyled
          focused={false}
          required={required || forceRequired}
          {...titleConfig}
        >
          {orderLabel}
          {label}
        </FormLabelStyled>
      ) : null}
      {description && descriptionPlacement === 'top' ? (
        <Typography
          my={1}
          color="text.secondary"
          variant="body2"
          {...descriptionConfig}
        >
          {description}
        </Typography>
      ) : null}
      <RadioGroup
        aria-label={label}
        name={field.name}
        value={field.value?.toString()}
        onChange={field.onChange}
        style={style}
        sx={{ alignItems: 'start' }}
      >
        {options
          ? options.map((item, index) => (
              <Box key={index.toString()}>
                <FormControlLabel
                  sx={{ alignItems: 'center' }}
                  labelPlacement={labelPlacement}
                  value={item.value?.toString()}
                  disabled={
                    disabled ||
                    item.disabled ||
                    forceDisabled ||
                    formik.isSubmitting
                  }
                  control={<RadioStyled disabled={item.disabled} />}
                  label={
                    <LabelStyled sx={{ my: 1 }}>
                      {item.icon ? (
                        <LineIcon
                          sx={{ marginRight: '8px' }}
                          icon={item.icon}
                        />
                      ) : null}
                      <Label text={item.label} hint={item.hint} />
                    </LabelStyled>
                  }
                />
                {item?.description ? (
                  <DescriptionStyled
                    variant="body2"
                    color={
                      disabled ||
                      item.disabled ||
                      forceDisabled ||
                      formik.isSubmitting
                        ? 'text.disabled'
                        : 'text.secondary'
                    }
                  >
                    {item.description}
                  </DescriptionStyled>
                ) : null}
              </Box>
            ))
          : null}
      </RadioGroup>
      {description && descriptionPlacement === 'bottom' ? (
        <Typography
          my={1}
          color="text.secondary"
          variant="body2"
          {...descriptionConfig}
        >
          {description}
        </Typography>
      ) : null}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </FormControl>
  );
};

export default RadioGroupField;
