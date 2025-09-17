/**
 * @type: formElement
 * name: form.element.AdminAuthHeader
 */

import { Typography, useTheme } from '@mui/material';
import React from 'react';
import { useGlobal } from '@metafox/framework';

export default function AdminAuthHeader() {
  const { i18n, assetUrl } = useGlobal();
  const theme = useTheme();

  const logo =
    theme.palette.mode === 'dark'
      ? assetUrl('layout.image_logo_dark')
      : assetUrl('layout.image_logo');

  return (
    <div>
      <div className={'text-center pb2'}>
        <img src={logo} alt="logo" style={{ height: 40 }} />
      </div>
      <div className="text-center pb1">
        <Typography component="h1" variant="h4">
          {i18n.formatMessage({
            id: 'sign_in_to_admincp',
            defaultMessage: 'Sign In AdminCP'
          })}
        </Typography>
      </div>
    </div>
  );
}
