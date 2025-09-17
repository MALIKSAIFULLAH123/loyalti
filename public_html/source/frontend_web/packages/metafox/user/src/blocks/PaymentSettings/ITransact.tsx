import { BlockViewProps } from '@metafox/framework';
import { TextField } from '@mui/material';
import React from 'react';
import useStyles from './styles';

export interface Props extends BlockViewProps {}

export default function ITransact() {
  const classes = useStyles();

  return (
    <div className={classes.itemPayment}>
      <div className={classes.itemTitle}>iTransact</div>
      <div className={classes.itemDescription}>
        iTransact is a 20 year old, full service payment processing company
        powered by the largest processors on the planet servicing millions,
        while at the same time providing personal, world-class customer support
        and assistance.
      </div>
      <div className={classes.itemForm}>
        <div className={classes.itemInfo}>
          <TextField
            label="Gateway Id"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="API Username"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="API key"
            variant="outlined"
            className={classes.input}
            size="small"
          />
        </div>
        <div className={classes.itemInfo}>
          <TextField
            label="iTransact Guideline"
            variant="outlined"
            className={classes.input}
            size="small"
            multiline
            defaultValue=""
          />
        </div>
      </div>
    </div>
  );
}
