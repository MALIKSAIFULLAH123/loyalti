import { styled } from '@mui/material';
import React from 'react';

interface Props {
  title: string;
}

const Root = styled('div')(({ theme }) => ({
  padding: theme.spacing(1.5, 0),
  fontWeight: theme.typography.fontWeightBold
}));

export default function BuddyHeader({ title }: Props) {
  return <Root>{title}</Root>;
}
