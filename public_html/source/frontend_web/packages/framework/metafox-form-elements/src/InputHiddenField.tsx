/**
 * @type: formElement
 * name: form.element.InputHidden
 * chunkName: formBasic
 */
import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

const InputHiddenField = ({ name, config }: FormFieldProps) => {
  const [field] = useField(name ?? 'HiddenField');

  return <input type='hidden' value={field.value} name={name} />;
};

export default InputHiddenField;
