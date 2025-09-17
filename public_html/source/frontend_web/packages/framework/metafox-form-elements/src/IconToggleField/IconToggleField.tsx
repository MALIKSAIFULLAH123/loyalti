/**
 * @type: formElement
 * name: form.element.IconToggle
 * chunkName: formBasic
 */

import { FormFieldProps } from '@metafox/form';
import {
  FormControl,
  FormControlLabel,
  Tooltip,
  Typography,
  styled
} from '@mui/material';
import clsx from 'clsx';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import useStyles from './styles';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';

const Label = styled(Typography, {
  name: 'Text'
})(({ theme }) => ({
  '& p': {
    color: theme.palette.text.hint
  }
}));

const IconStyled = styled(LineIcon, {
  name: 'IconStyled',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(20),
  marginLeft: theme.spacing(1),
  ...(active && {
    color: theme.palette.primary.main
  }),
  '&:hover': {
    color: theme.palette.primary.main
  }
}));

const SwitchField = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const classes = useStyles();

  const { i18n } = useGlobal();

  const [field, , { setValue }] = useField(name ?? 'IconToggle');

  const {
    margin = 'none',
    disabled,
    fullWidth,
    label,
    labelPlacement,
    tooltip,
    icon = 'ico-gear-o',
    variant = 'body1',
    componentLabel = 'span',
    color = 'text.primary',
    sxFieldWrapper,
    sxLabel
  } = config;

  const handleChange = () => {
    if (disabled || forceDisabled) return;

    setValue(field.value ? 0 : 1);
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
    >
      <FormControlLabel
        {...field}
        sx={sxFieldWrapper ? sxFieldWrapper : { color: 'text.primary' }}
        disabled={config.disabled || disabled}
        label={
          label && (
            <Label
              variant={variant}
              component={componentLabel}
              color={color}
              sx={sxLabel}
            >
              {label}
            </Label>
          )
        }
        labelPlacement={labelPlacement}
        className={clsx(classes.root, fullWidth && classes.fullWidth)}
        control={
          <Tooltip title={tooltip && i18n.formatMessage({ id: tooltip })}>
            <IconStyled
              icon={icon}
              onClick={handleChange}
              active={field.value}
            />
          </Tooltip>
        }
      />
    </FormControl>
  );
};

export default SwitchField;
