import { BlockViewProps } from '@metafox/framework';
import { TextField } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export interface Props extends BlockViewProps {}

export default function Braintree() {
  const classes = useStyles();

  return (
    <div className={classes.itemPayment}>
      <div className={classes.itemTitle}>Braintree</div>
      <div className={classes.itemDescription}>
        We provide the global commerce tools people need to build businesses,
        accept payments, and enable commerce for their users. Itâ€™s the
        simplest way to get paid for your great ideas
      </div>
      <div className={classes.itemForm}>
        <div className={classes.itemInfo}>
          <TextField
            label="Merchant ID"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="Public Key"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="Private Key"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="CSE Key"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
      </div>
    </div>
  );
}
