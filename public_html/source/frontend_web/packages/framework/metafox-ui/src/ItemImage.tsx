import { useBlock } from '@metafox/layout';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import clsx from 'clsx';
import { camelCase } from 'lodash';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        overflow: 'hidden'
      },
      width160: {
        maxWidth: 160,
        width: 160
      },
      width200: {
        maxWidth: 200,
        width: 200
      },
      fillHeight: {
        '& .MuiImage-root': {
          height: '100%'
        }
      },
      start: {
        '&.MuiItemText-root': {
          marginLeft: theme.spacing(2)
        }
      },
      end: {
        '&.MuiItemText-root': {
          marginRight: theme.spacing(2)
        }
      },
      top: {},
      bottom: {},
      spacingNormal: {
        padding: theme.spacing(2, 0, 2, 2)
      }
    }),
  {
    name: 'MuiItemImage'
  }
);

export default function ItemImage({ className, children }) {
  const classes = useStyles();

  const {
    itemProps: {
      media: { width, placement, fillHeight }
    }
  } = useBlock();

  return (
    <div
      role="img"
      data-testid="itemImage"
      className={clsx(
        classes.root,
        fillHeight && classes.fillHeight,
        width && classes[camelCase(`width-${width}`)],
        placement && classes[camelCase(`${placement}`)],
        className
      )}
    >
      {children}
    </div>
  );
}
