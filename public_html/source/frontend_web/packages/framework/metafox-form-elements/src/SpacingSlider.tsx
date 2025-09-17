import { Slider, Typography } from '@mui/material';
import { useField } from 'formik';
import React from 'react';

type Props = {
  name: string;
  value: number;
  min: number;
  max: number;
  label: string;
  valuetext: (value: number) => string;
  marks: { label: string; value: number }[];
};

export default function SpacingSlider({
  name,
  value,
  min = 0,
  max = 5,
  valuetext,
  label,
  marks
}: Props) {
  const [, , helpers] = useField(name ?? 'SpacingSlider');

  const onChange = (evt, value) => {
    helpers.setValue(value, true);
  };

  const [defaultValue] = React.useState(value);

  return (
    <div style={{ display: 'flex' }}>
      <div style={{ width: '60px', paddingTop: 4, fontSize: 12 }}>
        <Typography id="discrete-slider-restrict" variant="body2">
          {label}
        </Typography>
      </div>
      <div style={{ flex: 1 }}>
        <Slider
          defaultValue={defaultValue}
          onChange={onChange}
          getAriaValueText={valuetext}
          aria-labelledby="discrete-slider-restrict"
          max={max}
          min={min}
          step={1}
          valueLabelDisplay="off"
          marks={marks}
        />
      </div>
    </div>
  );
}
