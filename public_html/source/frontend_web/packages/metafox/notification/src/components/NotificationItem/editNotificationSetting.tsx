/**
 * @type: ui
 * name: notification.editNotificationSetting
 */

import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const Noti = styled('div')(({ theme }) => ({
  marginLeft: theme.spacing(2),
  color: theme.palette.primary.main,
  fontSize: theme.spacing(1.7),
  cursor: 'pointer',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const EditNotificationSetting = () => {
  const { i18n, dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({ type: 'notification/editNotificationSetting' });
  };

  return (
    <Noti onClick={handleClick}>{i18n.formatMessage({ id: 'settings' })}</Noti>
  );
};

export default EditNotificationSetting;
