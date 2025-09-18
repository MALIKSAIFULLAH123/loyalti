/**
 * @type: ui
 * name: page.ui.buttonActionHeaderProfile
 */

import { useGlobal } from '@metafox/framework';
import { Link } from '@mui/material';
import React from 'react';

export default function ButtonActionHeaderProfile(props) {
  const { dispatch, usePageParams, i18n } = useGlobal();
  const params = usePageParams();
  const { value, label, name } = props;

  const handleClick = () => {
    dispatch({
      type: value,
      payload: { identity: params?.identity }
    });
  };

  return (
    <Link
      sx={{ fontWeight: 400, cursor: 'pointer' }}
      color="primary"
      onClick={handleClick}
      data-testid={name || label}
    >
      {i18n.formatMessage({ id: label })}
    </Link>
  );
}
