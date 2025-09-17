/**
 * @type: ui
 * name: core.block.no_content_with_icon
 * title: No content with icon
 * keywords: no content
 */

import { useAppMenu, useGlobal, useLocation } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Theme, Button } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import { camelCase } from 'lodash';
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
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(24),
        fontWeight: theme.typography.fontWeightBold,
        marginBottom: theme.spacing(1.5),
        textAlign: 'center',
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
  title: string;
  description?: string;
  labelButton?: string;
  identity?: string;
  action?: string;
  isIconButton?: boolean;
  iconButton?: string;
}

export default function NoContentWithIcon({
  image: iconProp,
  title: titleProp,
  description,
  labelButton,
  prev_identity,
  action,
  isIconButton = true,
  iconButton
}: NoContentWithIconProps) {
  const classes = useStyles();
  const { i18n, usePageParams, dispatch } = useGlobal();
  const location = useLocation();

  const pageParams = usePageParams();

  const primaryMenu = useAppMenu('core', 'primaryMenu');

  const identity =
    pageParams?.heading?.props?.identity || `${prev_identity}${pageParams?.id}`;

  const icon =
    iconProp ||
    primaryMenu.items.find(
      item => item.to?.split('/')[1] === location.pathname.split('/')[1]
    )?.icon ||
    'ico-user-circle-o';
  const moduleName = pageParams.appName || pageParams.resourceName;
  const title =
    titleProp ||
    `no${pageParams?.tab ? `_${pageParams?.tab}` : ''}_${moduleName}_found`;

  const onAddNewItem = () => {
    dispatch({ type: action, payload: { identity } });
  };

  return (
    <div className={classes.root} data-testid="noResultFound">
      <LineIcon className={classes.icon} icon={icon} />
      <div className={classes.title}>
        <span data-testid={camelCase(`notFoundTitle ${moduleName} `)}>
          {i18n.formatMessage({ id: title })}
        </span>
      </div>
      {description ? (
        <div className={classes.content}>
          <span data-testid={camelCase(`notFoundDescription ${moduleName} `)}>
            {i18n.formatMessage({ id: description })}
          </span>
        </div>
      ) : null}
      {labelButton && (
        <Button
          data-testid={camelCase(`notFoundButton ${moduleName} `)}
          variant="contained"
          color="primary"
          startIcon={
            isIconButton ? <LineIcon icon={iconButton || 'ico-plus'} /> : null
          }
          sx={{ fontSize: 18, my: 2.5 }}
          onClick={onAddNewItem}
        >
          {i18n.formatMessage({ id: labelButton })}
        </Button>
      )}
    </div>
  );
}
