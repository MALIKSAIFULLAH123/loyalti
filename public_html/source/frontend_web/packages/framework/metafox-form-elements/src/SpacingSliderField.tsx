/**
 * @type: formElement
 * name: form.element.SpacingSlider
 */
import { Box, Slider, Typography, useTheme } from '@mui/material';
import { useField } from 'formik';
import { range } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

export default function SpacingSliderField({ config, name }: FormFieldProps) {
  const [, meta] = useField(name ?? 'SpacingSliderField');
  const { label, min = 0, max = 5 } = config;
  const theme = useTheme();

  const marks = range(min, max).map(value => ({
    value,
    label: value ? `${theme.spacing(value)}` : '0'
  }));

  const [, , helpers] = useField(name ?? 'SpacingSliderField');

  function valuetext(value: number) {
    return value ? `${theme.spacing(value)}` : '0';
  }

  const onChange = (evt, value) => {
    helpers.setValue(value, true);
  };

  const [defaultValue] = React.useState(meta.initialValue);

  return (
    <Box p={[2, 0, 1, 0]}>
      <Typography id="discrete-slider-restrict" variant="body1">
        {label}
      </Typography>
      <Box p={[0, 2, 0, 1]}>
        <Slider
          defaultValue={defaultValue}
          onChange={onChange}
          getAriaValueText={valuetext}
          aria-labelledby="discrete-slider-restrict"
          max={max}
          min={min}
          step={0.125}
          valueLabelDisplay="off"
          marks={marks}
        />
      </Box>
    </Box>
  );
}
