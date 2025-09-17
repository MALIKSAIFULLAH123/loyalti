/**
 * @type: formElement
 * name: form.element.MarginSpaceGroup
 */

import {
  Box,
  Grid,
  MenuItem,
  Select,
  Typography,
  useTheme
} from '@mui/material';
import { useField } from 'formik';
import { range } from 'lodash';
import * as React from 'react';

function SelectInput({ label, name, step = 0.125 }) {
  const [field, , { setValue }] = useField(name);
  const theme = useTheme();
  const options = range(0, 4 / step + 1).map(x => ({
    value: x * step,
    label: `${theme.spacing(x * step)}`
  }));

  return (
    <Select
      value={field.value}
      onBlur={field.onBlur}
      onChange={evt => setValue(Number(evt.target.value))}
      size="small"
      variant="outlined"
      defaultValue={0}
      fullWidth
      startAdornment={label}
    >
      {options.map(x => (
        <MenuItem key={x.label} value={x.value}>
          {x.label}
        </MenuItem>
      ))}
    </Select>
  );
}

export default function MarginSpaceGroupField({ config }) {
  const { label, prefix, description } = config;

  return (
    <Box sx={{ pt: 2, width: '100%' }}>
      {label ? (
        <Typography sx={{ fontWeight: 'bold', pb: 2 }}>{label}</Typography>
      ) : null}
      {description ? (
        <Typography paragraph variant="body2" color="text.secondary">
          {description}
        </Typography>
      ) : null}
      <Grid container spacing={2}>
        <Grid item xs={3}>
          <SelectInput label="top" name={`${prefix}.mt`} />
        </Grid>
        <Grid item xs={3}>
          <SelectInput label="right" name={`${prefix}.mr`} />
        </Grid>
        <Grid item xs={3}>
          <SelectInput label="bottom" name={`${prefix}.mb`} />
        </Grid>
        <Grid item xs={3}>
          <SelectInput label="left" name={`${prefix}.ml`} />
        </Grid>
      </Grid>
    </Box>
  );
}
