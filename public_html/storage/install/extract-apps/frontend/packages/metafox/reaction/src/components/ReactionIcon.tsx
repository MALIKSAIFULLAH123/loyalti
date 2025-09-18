import { styled } from '@mui/material';
import React from 'react';
import { ReactionIconProps } from '../types';

const ButtonStyled = styled('span')(({ theme }) => ({
  width: '100%',
  height: '100%'
}));

export default function ReactionIcon({
  classes,
  src,
  icon,
  title
}: ReactionIconProps) {
  return (
    <ButtonStyled role="button">
      <img src={src || icon} alt={title} />
    </ButtonStyled>
  );
}
