/**
 * @type: formElement
 * name: form.element.Switch
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import {
  Box,
  FormControl,
  FormControlLabel,
  Switch,
  styled
} from '@mui/material';
import clsx from 'clsx';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import Description from '../Description';
import Label from '../Label';
import Warning from '../Warning';
import useStyles from './styles';
import ErrorMessage from '../ErrorMessage';

const Text = styled(Box, {
  name: 'Text'
})(({ theme }) => ({
  '& p': {
    color: theme.palette.text.hint
  }
}));

const SwitchField = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const classes = useStyles();

  const [field, meta, { setValue }] = useField(name ?? 'SwitchField');
  const {
    label,
    margin,
    labelPlacement,
    checkedValue = 1,
    uncheckedValue = 0,
    size,
    color = 'primary',
    required,
    disabled,
    edge,
    fullWidth,
    warning,
    description,
    variant,
    sxLabel
  } = config;

  // eslint-disable-next-line eqeqeq
  const checked = Boolean(field.value == checkedValue ?? config.checked);

  const handleChange = (evt, checked: boolean) => {
    setValue(checked ? checkedValue : uncheckedValue);
  };

  const haveError: boolean = !!(
    meta.error &&
    (meta.touched || formik.submitCount)
  );

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
    >
      <FormControlLabel
        {...field}
        sx={sxLabel ? sxLabel : { color: 'text.primary' }}
        disabled={disabled || forceDisabled || formik.isSubmitting}
        label={<Label text={label} />}
        labelPlacement={labelPlacement}
        className={clsx(
          sxLabel ? classes.label : classes.root,
          fullWidth && classes.fullWidth
        )}
        control={
          <Switch
            color={color}
            size={size}
            disabled={disabled || forceDisabled || formik.isSubmitting}
            required={required}
            checked={checked}
            edge={edge}
            data-testid={camelCase(`input ${name}`)}
            className={classes.switch}
            onChange={handleChange}
            variant={variant}
          />
        }
      />
      {description ? (
        <Text>
          <Description sx={{ marginTop: '-12px' }} text={description} />
        </Text>
      ) : null}
      <Warning warning={warning} />
      {haveError && <ErrorMessage error={meta?.error} />}
    </FormControl>
  );
};

export default SwitchField;
