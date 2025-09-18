/**
 * @type: ui
 * name: group.ui.updateRoleUserButton
 * chunkName: group
 */

import { useGlobal } from '@metafox/framework';
import { Link } from '@mui/material';
import React from 'react';

export default function AddPhotoALbumButton(props) {
  const { dispatch, usePageParams, i18n } = useGlobal();
  const params = usePageParams();
  const { value, label } = props;

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
      data-testid={'updateRoleUserButton'}
    >
      {i18n.formatMessage({ id: label })}
    </Link>
  );
}
