/**
 * @type: formElement
 * name: form.element.TextResize
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import {
  FormControl,
  Slider,
  SliderValueLabelProps,
  Stack,
  TextFieldProps,
  Tooltip,
  Typography
} from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';

function ValueLabelComponent(props: SliderValueLabelProps) {
  const { children, value } = props;

  return (
    <Tooltip enterTouchDelay={0} placement="top" title={value}>
      {children}
    </Tooltip>
  );
}

const TextResizeField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const { i18n } = useGlobal();
  const [field, , { setValue }] = useField(name ?? 'text_size');

  const {
    variant,
    margin,
    fullWidth,
    sxFieldWrapper,
    label = 'text_size',
    defaultValue = 12,
    min = 12,
    max = 80,
    showTooltip = 'off'
  } = config;

  const handleChangeFontSize = e => {
    if (forceDisabled || formik.isSubmitting) return;

    setValue(e.target.value);
  };

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      disabled={forceDisabled || formik.isSubmitting}
    >
      <Typography gutterBottom>{i18n.formatMessage({ id: label })}</Typography>
      <Stack spacing={1.5} direction="row" sx={{ mb: 1 }} alignItems="center">
        <Slider
          disabled={forceDisabled || formik.isSubmitting}
          valueLabelDisplay={showTooltip}
          slots={{
            valueLabel: ValueLabelComponent
          }}
          aria-label="custom thumb label"
          defaultValue={defaultValue}
          value={field.value}
          onChange={handleChangeFontSize}
          min={min}
          max={max}
        />
        <span>{field.value || defaultValue}</span>
      </Stack>
    </FormControl>
  );
};

export default TextResizeField;
