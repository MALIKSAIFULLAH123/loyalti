/**
 * @type: formElement
 * name: form.element.DialogFooter
 * chunkName: formDialog
 */
import { DialogActions } from '@mui/material';
import React from 'react';
import Container from '../Container/Container';

export default function DialogFooter({ config }) {
  const { name, elements } = config;

  return (
    <Container
      config={{ elements, wrapAs: DialogActions, name, wrapperProps: {} }}
    />
  );
}
