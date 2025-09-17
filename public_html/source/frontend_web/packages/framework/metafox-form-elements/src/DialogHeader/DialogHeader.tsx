/**
 * @type: formElement
 * name: form.element.DialogHeader
 * chunkName: formDialog
 */
import { DialogTitle } from '@metafox/dialog';
import { map } from 'lodash';
import React from 'react';
import { Element, useFormSchema } from '@metafox/form';

export default function DialogHeader({ config: { elements, config }, formik }) {
  const { title } = useFormSchema();

  return (
    <DialogTitle
      disableClose
      sx={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      }}
      {...config}
    >
      {title}
      {map(elements, (config, key) => (
        <Element key={key.toString()} config={config} formik={formik} />
      ))}
    </DialogTitle>
  );
}
