/**
 * @type: formElement
 * name: form.element.BorderStyleGroup
 */
import {
  Box,
  FormControl,
  Grid,
  MenuItem,
  Select,
  Typography,
  SelectProps,
  InputLabel
} from '@mui/material';
import { useField } from 'formik';
import React from 'react';

function SelectInput({
  label,
  startAdornment,
  name,
  options
}: SelectProps<{ label: string; value: number | string }>) {
  const [field] = useField(name);

  return (
    <FormControl variant="outlined" size="small" sx={{ minWidth: 320 }}>
      <InputLabel>{label}</InputLabel>
      <Select
        startAdornment={startAdornment}
        {...field}
        size="small"
        label={label}
        variant="outlined"
        defaultValue={0}
      >
        {options.map(x => (
          <MenuItem key={x.label} value={x.value}>
            {x.label}
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  );
}

export default function MarginSpaceGroupField({ config, name }) {
  const widths = [
    { value: 0, label: '0px' },
    { value: 1, label: '1px' },
    { value: 2, label: '2px' },
    { value: 3, label: '3px' },
    { value: 4, label: '4px' },
    { value: 4, label: '4px' },
    { value: 5, label: '5px' },
    { value: 6, label: '6px' },
    { value: 7, label: '7px' },
    { value: 8, label: '8px' }
  ];

  const colors = [
    { label: 'None', value: 'transparent' },
    { label: 'Border Primary', value: 'border.primary' },
    { label: 'Border Secondary', value: 'border.secondary' }
  ];

  const styles = [
    { label: 'Solid', value: 'solid' },
    { label: 'Dashed', value: 'dashed' }
  ];

  return (
    <Box sx={{ pt: 1 }}>
      <Typography paragraph>Styling css border style</Typography>
      <Grid container spacing={2}>
        <Grid item>
          <SelectInput
            options={colors}
            label="Border Color"
            startAdornment={null}
            name={`${name}.borderColor`}
          />
        </Grid>
        <Grid item>
          <SelectInput
            options={styles}
            startAdornment={null}
            label="Border Style"
            name={`${name}.borderStyle`}
          />
        </Grid>
        <Grid item>
          <SelectInput
            options={widths}
            startAdornment="top"
            label="Border Top Width"
            name={`${name}.borderTopWidth`}
          />
        </Grid>
        <Grid item>
          <SelectInput
            options={widths}
            label="Border Right Width"
            startAdornment="right"
            name={`${name}.borderRightWidth`}
          />
        </Grid>
        <Grid item>
          <SelectInput
            options={widths}
            label="Border Left Width"
            startAdornment="left"
            name={`${name}.borderLeftWidth`}
          />
        </Grid>
        <Grid item>
          <SelectInput
            options={widths}
            label="Border Bottom Width"
            startAdornment="bottom"
            name={`${name}.borderBottomWidth`}
          />
        </Grid>
      </Grid>
    </Box>
  );
}
