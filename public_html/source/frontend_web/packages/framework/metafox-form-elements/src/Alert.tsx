/**
 * @type: formElement
 * name: form.element.Alert
 * chunkName: formBasic
 */

import { Alert } from '@mui/material';
import React from 'react';
import HtmlViewer from '@metafox/html-viewer';
import { FormFieldProps } from '@metafox/form';

export default function AlertField({
  config,
  name,
  disabled: forceDisabled,
  required: forceRequired,
  formik
}: FormFieldProps) {
  const { message, color, variant = 'standard', sx = {}, ...rest } = config;

  return (
    <Alert
      color={color}
      variant={variant}
      sx={{ fontSize: 'small', my: 1, p: 0.5, alignItems: 'center', ...sx }}
      {...rest}
    >
      <HtmlViewer html={message || ''} />
    </Alert>
  );
}
