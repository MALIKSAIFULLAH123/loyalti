/**
 * @type: ui
 * name: photo.ui.addPhotoAlbum
 */

import { useGlobal } from '@metafox/framework';
import { Link } from '@mui/material';
import React from 'react';

export default function AddPhotoALbumButton() {
  const { i18n, dispatch, usePageParams } = useGlobal();
  const params = usePageParams();

  const handleClick = () => {
    dispatch({
      type: 'photo/presentCreateNewAlbum',
      payload: { id: params?.id }
    });
  };

  return (
    <Link
      sx={{ fontWeight: 400, cursor: 'pointer' }}
      color="primary"
      onClick={handleClick}
      data-testid={'addPhotoAlbumButton'}
    >
      {i18n.formatMessage({ id: 'add_album' })}
    </Link>
  );
}
