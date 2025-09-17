/**
 * @type: formElement
 * name: form.element.Typo
 * chunkName: formBasic
 */

import { Typography } from '@mui/material';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import HtmlViewer from '@metafox/html-viewer';

const TypoHtml = ({ config }: FormFieldProps) => {
  // confuse property name plainText but API response html
  const { plainText, component, elements, tagName, ...restConfig } = config;

  return (
    <Typography sx={{ mt: 2 }} component={tagName as any} {...restConfig}>
      <HtmlViewer html={plainText || ''} />
    </Typography>
  );
};

export default TypoHtml;
