/**
 * @type: formElement
 * name: form.element.Hidden
 * chunkName: formBasic
 */
import { useField } from 'formik';
import React from 'react';
import { FormFieldProps } from '@metafox/form';

const HiddenField = ({ name, config }: FormFieldProps) => {
  const [, , { setValue }] = useField(name ?? 'HiddenField');

  React.useEffect(() => {
    if (config.value || config.defaultValue) {
      setValue(config.value);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return null;
};

export default HiddenField;
