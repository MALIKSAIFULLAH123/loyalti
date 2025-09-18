/**
 * @type: ui
 * name: marketplace.ui.paymentButton
 */

import React from 'react';
import { Button } from '@mui/material';
import { PaymentButtonShape } from '@metafox/marketplace/types';
import { useGlobal } from '@metafox/framework';

interface Props extends PaymentButtonShape {
  identity: string;
  pagingId?: string;
}
export default function PaymentButtonItem({
  variant,
  color,
  value,
  label,
  identity,
  pagingId
}: Props) {
  const { dispatch } = useGlobal();
  const handlePaymentButton = React.useCallback(() => {
    if (!value) return;

    dispatch({ type: value, payload: { identity, pagingId } });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value, identity]);

  return (
    <Button
      variant={variant || 'outlined'}
      color={color || 'primary'}
      onClick={handlePaymentButton}
    >
      {label}
    </Button>
  );
}
