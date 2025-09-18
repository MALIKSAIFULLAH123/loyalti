import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const UICallView = styled('div', { slot: 'uiCallView' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-around',
  alignItems: 'center',
  flexDirection: 'column',
  maxWidth: '100%',
  overflowX: 'hidden',
  boxSizing: 'border-box',
  position: 'fixed',
  left: 0,
  right: 0,
  top: 0,
  bottom: 0,

  backgroundColor: theme.palette.grey['800'],

  '& h1, h2, h3,h4, h5, h6': {
    color: 'white'
  }
}));

const ConnectingView = () => {
  const { i18n } = useGlobal();

  return (
    <UICallView>
      <h1>{i18n.formatMessage({ id: 'connecting_dots' })}</h1>
    </UICallView>
  );
};
export default ConnectingView;
