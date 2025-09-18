/**
 * @type: formElement
 * name: form.element.WebcamPlayer
 * chunkName: formElement.livestreaming
 */

import { FormFieldProps } from '@metafox/form/types';
import { FormControl } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import { useField } from 'formik';
import WebcamPlayer from './Base';

export default function WebcamPlayerField({
  config: { size, margin = 'normal', fullWidth, sxFieldWrapper, streamKey },
  name,
  formik
}: FormFieldProps) {
  const [, , { setValue }] = useField(name ?? 'MuxVideoField');

  const onReady = data => {
    const { video, audio } = data || {};
    setValue({ video, audio });
  };

  return (
    <FormControl
      size={size}
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <WebcamPlayer onReady={onReady} sxDeviceWrapper={{ mt: 2 }} />
    </FormControl>
  );
}
