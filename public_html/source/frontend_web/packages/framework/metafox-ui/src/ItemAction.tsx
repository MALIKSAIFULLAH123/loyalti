import { Box, BoxProps } from '@mui/material';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import React from 'react';

type ActionProps = {
  visible?: 'hover' | 'always';
  spacing?: 'normal' | 'dense' | 'none';
  placement?:
    | 'top-end'
    | 'top-start'
    | 'bottom-end'
    | 'bottom-start'
    | 'center-end';
};

const ItemAction = styled(Box, {
  name: 'ItemView',
  slot: 'action',
  shouldForwardProp: prop =>
    prop !== 'placement' && prop !== 'spacing' && prop !== 'visible'
})<ActionProps>(
  ({ spacing, visible, placement, theme }) =>
    Object.assign(
      { position: 'relative' },
      visible === 'hover' && {
        opacity: 0,
        transition: 'opacity 0.3s'
      },
      spacing === 'normal' && {
        padding: theme.spacing(2)
      },
      spacing === 'none' && {
        padding: theme.spacing(0, 0, 0, 2)
      },
      placement === 'bottom-end' && {
        position: 'absolute',
        right: -8,
        bottom: -8
      },
      placement === 'top-end' && {
        position: 'absolute',
        right: -8,
        top: -6
      },
      placement === 'center-end' && {
        position: 'absolute',
        right: -8,
        top: '50%',
        transform: 'translate(0, -50%)'
      }
    ) as {}
);

const ItemActionRoot = ({ className, ...props }: ActionProps & BoxProps) => {
  return (
    <ItemAction
      data-testid="buttonActionMenu"
      className={clsx('ItemView-action', className)}
      {...props}
    />
  );
};

export default ItemActionRoot;
