/**
 * @type: formElement
 * name: form.element.MarkSlider
 */
import Slider from '@mui/material/Slider';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';

const MarkSlideField = ({ config, name }) => {
  const [field] = useField(name ?? 'MarkSlideField');
  const { options, min, max } = config;
  const getAriaValueText = (value: number): string => `${value}`;

  return (
    <Slider
      data-testid={camelCase(`field ${name}`)}
      defaultValue={0}
      getAriaValueText={getAriaValueText}
      aria-labelledby="discrete-slider"
      valueLabelDisplay="auto"
      step={null}
      marks={options}
      min={min}
      max={max}
      onChange={field.onChange}
      onBlur={field.onBlur}
    />
  );
};

export default MarkSlideField;
