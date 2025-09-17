import { BlockViewProps } from '@metafox/framework';
import { TextField } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export interface Props extends BlockViewProps {}

export default function BitPay() {
  const classes = useStyles();

  return (
    <div className={classes.itemPayment}>
      <div className={classes.itemTitle}>BitPay</div>
      <div className={classes.itemDescription}>
        BitPay was founded in 2011, while Bitcoin was still in its infancy. We
        saw the potential for bitcoin to revolutionize the financial industry,
        making payments faster, more secure, and less expensive on a global
        scale.
      </div>
      <div className={classes.itemForm}>
        <div className={classes.itemInfo}>
          <TextField
            label="Merchant API Key"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
      </div>
    </div>
  );
}
