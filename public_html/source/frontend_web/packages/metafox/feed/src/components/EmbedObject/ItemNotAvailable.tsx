import { useGlobal } from '@metafox/framework';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        backgroundColor: theme.palette.background.default,
        padding: theme.spacing(2),
        paddingBottom: 0
      },
      headerInfo: {
        marginBottom: theme.spacing(2),
        fontSize: '0.9375rem',
        padding: '4px 0',
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold
      },

      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(2)
      }
    }),
  { name: 'MuiItemNotAvailable' }
);

const ItemNotAvailable = () => {
  const { i18n } = useGlobal();
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <div className={classes.headerInfo}>
        {i18n.formatMessage({ id: 'content_not_available_title' })}
      </div>
      <div className={classes.statusRoot}>
        {i18n.formatMessage({ id: 'content_not_available_content' })}
      </div>
    </div>
  );
};

export default ItemNotAvailable;
