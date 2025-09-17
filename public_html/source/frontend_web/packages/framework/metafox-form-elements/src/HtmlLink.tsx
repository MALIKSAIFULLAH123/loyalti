/**
 * @type: formElement
 * name: form.element.HtmlLink
 * chunkName: formBasic
 */
import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import { useFormikContext } from 'formik';
import { useFormSchema } from '@metafox/form';

const HtmlLink = ({ config, formik }) => {
  const {
    href: to,
    name,
    label,
    action,
    actionPayload,
    sx = {},
    ...rest
  } = config;
  const { onCancel } = useFormSchema();
  const { dispatch } = useGlobal();
  const { setFieldValue } = useFormikContext();

  const handleClick = e => {
    if (action) {
      e.preventDefault();
      dispatch({
        type: action,
        payload: actionPayload,
        meta: { setFieldValue, onCancel }
      });
    }
  };

  return (
    <Link
      to={to}
      onClick={action ? handleClick : null}
      children={label}
      sx={{ cursor: 'pointer', ...sx }}
      {...rest}
    />
  );
};

export default HtmlLink;
