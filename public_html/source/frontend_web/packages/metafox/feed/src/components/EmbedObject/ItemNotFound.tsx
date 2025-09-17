/**
 * @type: ui
 * name: itemNotFound.embedItem.insideFeedItem
 */
import { useGlobal } from '@metafox/framework';
import { Box, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        backgroundColor:
          theme.palette.mode === 'light'
            ? theme.palette.background.default
            : theme.palette.action.hover,
        paddingTop: theme.spacing(4),
        paddingLeft: theme.spacing(2),
        paddingRight: theme.spacing(2),
        paddingBottom: 0,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        '&:last-child': {
          marginBottom: theme.spacing(2)
        }
      },
      headerInfo: {
        marginBottom: theme.spacing(2),
        fontSize: theme.mixins.pxToRem(24),
        color: theme.palette.text.primary,
        fontWeight: theme.typography.fontWeightBold
      },
      statusRoot: {
        display: 'block',
        marginBottom: theme.spacing(4.5),
        fontSize: theme.mixins.pxToRem(18),
        color: theme.palette.text.secondary
      }
    }),
  { name: 'MuiItemNotFound' }
);

export type ItemNotFoundProps = {
  title?: string;
  description?: string;
  sx?: any;
};

const ItemNotFound = ({ title, description, sx }: ItemNotFoundProps) => {
  const { i18n } = useGlobal();
  const classes = useStyles();

  return (
    <Box className={classes.root} sx={sx}>
      <div className={classes.headerInfo}>
        {i18n.formatMessage({ id: title ?? 'no_post_found_title' })}
      </div>
      <div className={classes.statusRoot}>
        {i18n.formatMessage({ id: description ?? 'no_post_found_content' })}
      </div>
    </Box>
  );
};

export default ItemNotFound;
