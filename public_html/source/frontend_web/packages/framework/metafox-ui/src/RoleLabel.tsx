import { Chip, SxProps } from '@mui/material';
import React from 'react';
import { Theme } from '@mui/material/styles';

type Props = {
  text: string;
  sx?: SxProps<Theme>;
};

export default function RoleLabel({
  text,
  sx,
  size = 'small',
  ...rest
}: Props) {
  return <Chip sx={sx} label={text} size={size} {...rest} />;
}
