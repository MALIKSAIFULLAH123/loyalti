/**
 * @type: siteDock
 * name: ToastContainer
 */
import { useGlobal } from '@metafox/framework';
import { ModalItemParams } from '@metafox/dialog';
import { styled } from '@mui/material';
import { uniqueId } from 'lodash';
import React, { useCallback, useEffect, useState } from 'react';
import { ToastItemShape } from './types';

const ToastWrapper = styled('div', { name: 'ToastWrapper', slot: 'root' })(
  ({ theme }) => ({
    position: 'fixed',
    bottom: 16,
    width: 'auto',
    left: '50%',
    transform: 'translateX(-50%)',
    padding: theme.spacing(0, 2),
    zIndex: 1301,
    '& > .toastItem + .toastItem': {
      marginTop: theme.spacing(0.5)
    },
    [theme.breakpoints.down('sm')]: {
      maxWidth: '90%',
      width: 'max-content',
      textAlign: 'center'
    }
  })
);

/**
 *
 * @returns JSX.ELement
 */
export default function ToastContainer(): JSX.Element {
  const { toastBackend, jsxBackend } = useGlobal();
  const [item, setItem] = useState<ModalItemParams>();

  const handleClose = useCallback((id: string) => {
    setItem(undefined);
  }, []);

  const handleItemChange = useCallback((item: ToastItemShape) => {
    setItem({
      component: 'ui.toast.item',
      props: {
        ...item,
        key: uniqueId(),
        className: 'toastItem',
        onClose: handleClose,
        id: uniqueId()
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    toastBackend?.setBroker && toastBackend.setBroker(handleItemChange);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!item) return null;

  return <ToastWrapper>{jsxBackend.render(item)}</ToastWrapper>;
}
