import { useAppMenu, RouteLink as Link } from '@metafox/framework';
import React from 'react';
import { Box } from '@mui/material';
import { styled } from '@mui/system';
import { APP_USER } from '@metafox/user';

const StyleLink = styled(Link)(({ theme }) => ({
  marginLeft: theme.spacing(2),
  fontSize: theme.mixins.pxToRem(16)
}));

const StyleBox = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginLeft: 'auto'
}));

export default function AppBarRegisterPage() {
  const items = useAppMenu(APP_USER, 'headerSignInMenu').items;

  return (
    <StyleBox>
      {items.map((item, index) => (
        <StyleLink key={index} to={item.to} color="primary">
          {item.label}
        </StyleLink>
      ))}
    </StyleBox>
  );
}
