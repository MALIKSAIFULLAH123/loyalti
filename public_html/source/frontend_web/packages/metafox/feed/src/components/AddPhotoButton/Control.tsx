import { useGlobal } from '@metafox/framework';
import { Link } from '@mui/material';
import React from 'react';

export default function Control({ onClick, testid, label, canUploadTypes }) {
  const { i18n } = useGlobal();

  return (
    <Link
      sx={{ fontWeight: 400, cursor: 'pointer' }}
      color="primary"
      onClick={onClick}
      data-testid={testid}
    >
      {i18n.formatMessage(
        { id: label || 'add_photos_video' },
        canUploadTypes
          ? {
              allowVideo: canUploadTypes?.video ? 1 : 0,
              allowPhoto: canUploadTypes?.photo ? 1 : 0
            }
          : {}
      )}
    </Link>
  );
}
