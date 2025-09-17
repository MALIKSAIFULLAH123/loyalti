/**
 * @type: formElement
 * name: form.element.DialogContent
 * chunkName: formDialog
 */
import { DialogContent as MuiDialogContent } from '@metafox/dialog';
import React from 'react';
import Container from '../Container';

export default function DialogContent({ config }) {
  const { name, elements } = config;

  return (
    <Container
      config={{
        elements,
        wrapAs: MuiDialogContent,
        name,
        wrapperProps: {}
      }}
    />
  );
}
