/* eslint-disable no-confusing-arrow */
/* eslint-disable no-unused-vars, @typescript-eslint/no-unused-vars */
import { useGlobal } from '@metafox/framework';
import { when } from '@metafox/utils';
import { useFormikContext } from 'formik';
import React, { useEffect, useState } from 'react';
import { withFormSchema } from './schema';
import { transformComponentName } from './transformComponentName';

const ElementRenderer = ({ config, formik }) => {
  const {
    component,
    name,
    validate,
    enabledWhen,
    showWhen,
    requiredWhen,
    template,
    acceptWhen,
    accept
  } = config;
  const { values } = useFormikContext();
  const { jsxBackend } = useGlobal();
  const [show, setShow] = useState(showWhen ? false : true);
  const [enabled, setEnabled] = useState(enabledWhen ? false : true);
  const [required, setRequired] = useState(requiredWhen ? false : true);
  const [acceptTypeFile, setAcceptTypeFile] = useState(accept);

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
      showWhen ? when(values, showWhen) : true,
      enabledWhen ? when(values, enabledWhen) : true,
      requiredWhen ? when(values, requiredWhen) : false,
      acceptWhen ? acceptFunction() : accept
    ]).then(([show, enabled, required, acceptResult]) => {
      setShow(show);
      setEnabled(enabled);
      setRequired(required);
      setAcceptTypeFile(acceptResult);
    });
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

  const actualName = transformComponentName(component);
  const FieldComponent = jsxBackend.get(`form.element.${actualName}`);

  if (!FieldComponent) {
    return null;
  }

  return React.createElement(FieldComponent, {
    config,
    name,
    disabled: !enabled,
    required,
    acceptTypeFile,
    formik
  });
};

export default withFormSchema(ElementRenderer);
