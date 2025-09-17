/**
 * @type: formElement
 * name: form.element.Divider
 * chunkName: formBasic
 */
import { Divider } from '@mui/material';
import React from 'react';

export default function FieldDivider({ config }) {
  const { sx } = config;

  return <Divider sx={sx}/>;
}
