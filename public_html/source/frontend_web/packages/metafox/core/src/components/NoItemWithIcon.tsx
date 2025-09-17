/**
 * @type: ui
 * name: core.block.no_item_with_icon
 * title: No content with description
 * keywords: no content
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
        padding: theme.spacing(2, 2, 0.25, 2),
        alignItems: 'center'
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
        [theme.breakpoints.down('xs')]: {
          fontSize: theme.mixins.pxToRem(18)
        }
      },
      content: {
        fontSize: theme.mixins.pxToRem(18),
        color: theme.palette.text.secondary,
        textAlign: 'center',
        [theme.breakpoints.down('xs')]: {
          fontSize: theme.mixins.pxToRem(15)
        }
      }
    }),
  {
    name: 'NoContentWithIcon'
  }
);

interface NoContentWithIconProps {
  image: string;
  title?: string;
  description?: string;
}

export default function NoContentWithIcon({
  image,
  title,
  description
}: NoContentWithIconProps) {
  const classes = useStyles();
  const { i18n } = useGlobal();

  return (
    <div className={classes.root} data-testid="noResultFound">
      <LineIcon className={classes.icon} icon={image} />
      {title && (
        <div className={classes.title}>
          <span>{i18n.formatMessage({ id: title })}</span>
        </div>
      )}
      {description && (
        <div className={classes.content}>
          {i18n.formatMessage({ id: description })}
        </div>
      )}
    </div>
  );
}
