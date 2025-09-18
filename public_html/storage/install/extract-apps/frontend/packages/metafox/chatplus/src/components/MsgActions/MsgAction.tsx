import { Button } from '@mui/material';
import React from 'react';

interface MsgActionProps {
  label?: string;
  onClick: () => void;
  children?: React.ReactChildren;
  className?: string;
}
export default function MsgAction({
  label,
  children,
  onClick,
  className
}: MsgActionProps) {
  return (
    <Button
      className={className}
      onClick={onClick}
      disableFocusRipple
      disableRipple
      disableTouchRipple
    >
      {label ? label : null}
      {children}
    </Button>
  );
}
