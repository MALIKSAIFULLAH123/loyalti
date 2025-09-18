/**
 * @type: ui
 * name: subscription.ui.paymentButton
 */

import React from 'react';
import { Button } from '@mui/material';
import { PaymentButtonShape } from '@metafox/subscription/types';
import { useGlobal } from '@metafox/framework';

interface Props extends PaymentButtonShape {
  identity: string;
}
export default function PaymentButtonItem({
  variant,
  color,
  value,
  label,
  identity,
  pagingId,
  size = 'medium'
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
      size={size}
    >
      {label}
    </Button>
  );
}
