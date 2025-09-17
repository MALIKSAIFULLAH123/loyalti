/**
 * @type: block
 * name: core.block.comingSoon
 * title: No content with coming soon
 * keywords: general
 * experiment: true
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        padding: theme.spacing(0, 2),
        alignItems: 'center',
        marginTop: theme.spacing(11.25)
      },
      icon: {
        fontSize: theme.mixins.pxToRem(72),
        color: theme.palette.text.secondary,
        marginBottom: theme.spacing(4)
      },
      title: {
        fontSize: theme.mixins.pxToRem(24),
        fontWeight: theme.typography.fontWeightBold,
        marginBottom: theme.spacing(1.5),
        textAlign: 'center',
        [theme.breakpoints.down('xs')]: {
          fontSize: theme.mixins.pxToRem(18)
        }
      }
    }),
  {
    name: 'ComingSoon'
  }
);

export default function ComingSoon() {
  const classes = useStyles();
  const { i18n } = useGlobal();

  return (
    <div className={classes.root}>
      <LineIcon className={classes.icon} icon={'ico-smile-o'} />
      <div className={classes.title}>
        <span>{i18n.formatMessage({ id: 'this_feature_is_coming_soon' })}</span>
      </div>
    </div>
  );
}
