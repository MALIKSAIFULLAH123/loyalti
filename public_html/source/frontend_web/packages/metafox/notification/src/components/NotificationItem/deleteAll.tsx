/**
 * @type: ui
 * name: notification.deleteAll
 */

import { useGlobal } from '@metafox/framework';
import { NOTIFICATION_PAGING_IDS } from '@metafox/notification/constant';
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
    dispatch({
      type: 'notification/deleteAll',
      payload: {
        pagingId: NOTIFICATION_PAGING_IDS
      }
    });
  };

  return (
    <Noti onClick={handleClick}>
      {i18n.formatMessage({ id: 'delete_all_notifications' })}
    </Noti>
  );
};

export default MarkAllAsRead;
