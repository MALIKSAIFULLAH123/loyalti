/**
 * @type: formElement
 * name: form.element.AddTextStyle
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import {
  Box,
  FormControl,
  TextFieldProps,
  Typography,
  styled
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, uniqueId } from 'lodash';
import React from 'react';

const Container = styled(Box, {
  shouldForwardProp: props => props !== 'disabled'
})<{ disabled?: boolean }>(({ theme, disabled }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  padding: theme.spacing(1),
  ...(!disabled && {
    '&:hover': {
      backgroundColor: theme.palette.action.hover,
      borderRadius: theme.shape.borderRadius
    }
  })
}));
const Label = styled(Typography)(({ theme }) => ({
  fontWeight: theme.typography.fontWeightMedium
}));
const LineIconStyled = styled(Box)(({ theme }) => ({
  padding: theme.spacing(1.25),
  borderRadius: '50%',
  marginRight: theme.spacing(1.5),
  backgroundColor: theme.palette.background.default,
  ...(theme.palette.mode === 'dark' && {
    border: theme.mixins.border('secondary')
  }),
  '& span': {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

const TextFormField = ({
  config,
  disabled: forceDisabled,
  required: forceRequired,
  name,
  formik
}: FormFieldProps<TextFieldProps>) => {
  const { i18n } = useGlobal();
  const [, , { setValue }] = useField(name ?? 'add_text');

  const { variant, margin, fullWidth, sxFieldWrapper, label } = config;

  const handleClick = () => {
    if (forceDisabled || formik.isSubmitting) return;

    const key = uniqueId(name || 'add_text');

    setValue(key);
  };

  return (
    <FormControl
      margin={margin}
      variant={variant}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
      id={name}
      disabled={forceDisabled || formik.isSubmitting}
    >
      <Container
        disabled={forceDisabled || formik.isSubmitting}
        onClick={handleClick}
      >
        <LineIconStyled>
          <span>{i18n.formatMessage({ id: 'Aa' })}</span>
        </LineIconStyled>
        <Label>{label}</Label>
      </Container>
    </FormControl>
  );
};

export default TextFormField;
