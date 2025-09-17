import { BlockViewProps } from '@metafox/framework';
import { TextField } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export interface Props extends BlockViewProps {}

export default function GoPay() {
  const classes = useStyles();

  return (
    <div className={classes.itemPayment}>
      <div className={classes.itemTitle}>GoPay</div>
      <div className={classes.itemDescription}>
        GoPay is payment gateway operating in Czech Republic and Slovakia.
      </div>
      <div className={classes.itemForm}>
        <div className={classes.itemInfo}>
          <TextField
            label="Client ID"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="Client Secret"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="Go Pay Id"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
      </div>
    </div>
  );
}
