/**
 * @type: formElement
 * name: form.element.HiddenImageCaptcha
 * chunkName: formBasic
 */

import { FormFieldProps } from '@metafox/form';
import { FormControl } from '@mui/material';
import { useField } from 'formik';
import { camelCase } from 'lodash';
import React from 'react';
import ErrorMessage from '../ErrorMessage';

const CaptchaImageField = ({
  config,
  disabled: forceDisabled,
  name,
  formik
}: FormFieldProps) => {
  const { margin, fullWidth, disabled } = config;
  const [, meta] = useField(name ?? 'image_captcha');

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));

  if (disabled || forceDisabled) {
    return null;
  }

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={{ margin: 0 }}
    >
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </FormControl>
  );
};

export default CaptchaImageField;
