/**
 * @type: formElement
 * name: form.element.BlockHeaderActions
 */
import { InputNotched } from '@metafox/ui';
import {
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  Theme,
  useTheme
} from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import { useField } from 'formik';
import { range } from 'lodash';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        flex: 1,
        borderRadius: 4
      },
      control: {
        marginRight: 8,
        '& fieldset': {
          border: 'none !important'
        }
      }
    }),
  {
    name: 'MarginSpaceGroupField'
  }
);

function SelectInput({ label, name, step = 0.125 }) {
  const [field] = useField(name);
  const theme = useTheme();
  const options = range(0, 3 / step).map(x => ({
    value: x * step,
    label: `${label}: ${theme.spacing(x * step)}`
  }));

  return (
    <FormControl variant="outlined" size="small">
      <Select {...field} size="small" variant="outlined" defaultValue={0}>
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
  const classes = useStyles();
  const { label } = config;

  return (
    <FormControl margin="normal" variant="outlined" fullWidth>
      <InputLabel variant="outlined" shrink>
        {label}
      </InputLabel>
      <div className={classes.root}>
        <div className={classes.control}>
          <SelectInput label="top" name={`${name}.mt`} />
        </div>
        <div className={classes.control}>
          <SelectInput label="right" name={`${name}.mr`} />
        </div>
        <div className={classes.control}>
          <SelectInput label="bottom" name={`${name}.mb`} />
        </div>
        <div className={classes.control}>
          <SelectInput label="left" name={`${name}.ml`} />
        </div>
      </div>
      <InputNotched variant="outlined" children={label} />
    </FormControl>
  );
}
