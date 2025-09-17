import { FormFieldProps } from '@metafox/form/types';
import { useGlobal } from '@metafox/framework';
import { when } from '@metafox/utils';
import { useFormikContext } from 'formik';
import React, { useEffect, useState } from 'react';
import { withFormSchema } from './FormSchema';

const ElementRenderer = ({
  config,
  formik,
  handleSubmitField
}: FormFieldProps) => {
  const {
    component,
    name,
    enabledWhen,
    showWhen,
    requiredWhen,
    acceptWhen,
    accept
  } = config;
  const { values } = useFormikContext();
  const { jsxBackend, useLayoutPageSize } = useGlobal();
  const [show, setShow] = useState(!showWhen);
  const [enabled, setEnabled] = useState(!enabledWhen);
  const [required, setRequired] = useState(!requiredWhen);
  const [acceptTypeFile, setAcceptTypeFile] = useState(accept);
  const mediaScreen = useLayoutPageSize();

  /**
   * When the values have changed process conditions on fields,
   * to decide whether to show and/or enable them or not.
   */

  const acceptFunction = () => {
    try {
      for (const [type, conditions] of Object.entries(acceptWhen)) {
        return when(values, conditions) ? type : accept;
      }
    } catch (err) {
      return accept;
    }
  };

  useEffect(() => {
    Promise.all([
      showWhen ? when({ ...values, mediaScreen }, showWhen) : true,
      enabledWhen ? when(values, enabledWhen) : true,
      requiredWhen ? when(values, requiredWhen) : false,
      acceptWhen ? acceptFunction() : accept
    ]).then(([show, enabled, required, acceptResult]) => {
      setShow(show);
      setEnabled(enabled);
      setRequired(required);
      setAcceptTypeFile(acceptResult);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    values,
    enabledWhen,
    showWhen,
    requiredWhen,
    required,
    accept,
    acceptWhen
  ]);

  if (!show) {
    return null;
  }

  const FieldComponent = jsxBackend.get(`form.element.${component}`);

  if (!FieldComponent) {
    return null;
  }

  return React.createElement(FieldComponent, {
    config,
    name,
    disabled: !enabled,
    required,
    acceptTypeFile,
    formik,
    handleSubmitField
  });
};

export default withFormSchema(ElementRenderer);
