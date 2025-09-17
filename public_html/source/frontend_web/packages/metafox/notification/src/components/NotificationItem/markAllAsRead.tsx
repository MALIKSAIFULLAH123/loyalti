/**
 * @type: ui
 * name: notification.markAllAsRead
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

const MarkAllAsRead = () => {
  const { i18n, dispatch } = useGlobal();

  const handleClick = () => {
    dispatch({ type: 'notification/markAllAsRead' });
  };

  return (
    <Noti onClick={handleClick}>
      {i18n.formatMessage({ id: 'mark_all_as_read' })}
    </Noti>
  );
};

export default MarkAllAsRead;
