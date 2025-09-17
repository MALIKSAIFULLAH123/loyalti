import { RefOf, useGlobal } from '@metafox/framework';
import { Popper as MuiPopper, PopperProps } from '@mui/material';
import React from 'react';

export type Props = PopperProps & {
  anchorRef?: RefOf<HTMLDivElement>;
  children?: React.ReactNode;
  zIndex?: number | string;
};

function Popper({ children, style = {}, zIndex, ...rest }: Props, ref) {
  const { useDialog, useInAppBar, useTheme } = useGlobal();
  const { dialogProps } = useDialog();
  const [inAppBar] = useInAppBar();
  const theme = useTheme();

  const { open: openDialog } = dialogProps || {};

  return (
    <MuiPopper
      style={{
        zIndex:
          zIndex ||
          (openDialog || inAppBar
            ? theme.zIndex.modal
            : theme.zIndex.speedDial),
        ...style
      }}
      ref={ref}
      {...rest}
    >
      {children}
    </MuiPopper>
  );
}

export default React.forwardRef(Popper);
