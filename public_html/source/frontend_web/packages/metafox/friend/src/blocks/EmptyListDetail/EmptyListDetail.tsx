/**
 * @type: ui
 * name: friend.block.empty_list
 * title: Friend list no content
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Theme, Button } from '@mui/material';
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
  title,
  description,
  labelButton,
  action,
  isIconButton = true,
  iconButton
}: NoContentWithIconProps) {
  const classes = useStyles();
  const { i18n, usePageParams, dispatch, getAcl } = useGlobal();

  const { list_id } = usePageParams();

  const identity = `friend.entities.friend_list.${list_id}`;

  const icon = iconProp || 'ico-user-circle-o';
  const canUpdate = getAcl('friend.friend_list.update');

  const onAddNewItem = () => {
    dispatch({ type: action, payload: { identity } });
  };

  return (
    <div className={classes.root}>
      <LineIcon className={classes.icon} icon={icon} />
      <div className={classes.title}>
        <span>{i18n.formatMessage({ id: title })}</span>
      </div>
      {description ? (
        <div className={classes.content}>
          {i18n.formatMessage({ id: description })}
        </div>
      ) : null}
      {labelButton && canUpdate && (
        <Button
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
