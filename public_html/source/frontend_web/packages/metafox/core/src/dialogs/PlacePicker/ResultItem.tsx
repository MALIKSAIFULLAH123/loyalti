import { LineIcon } from '@metafox/ui';
import { Theme } from '@mui/material';
import { createStyles, withStyles } from '@mui/styles';
import classNames from 'classnames';
import React from 'react';

const styles = (theme: Theme) =>
  createStyles({
    root: {
      borderBottom: '1px solid',
      borderBottomColor: theme.palette.border?.secondary,
      padding: theme.spacing(2),
      display: 'flex',
      alignItems: 'flex-start',
      '&:hover': {
        backgroundColor: theme.palette.action.hover
      }
    },
    info: {
      display: 'flex',
      flexDirection: 'column'
    },
    title: {
      flex: 1,
      fontSize: theme.mixins.pxToRem(15),
      fontWeight: theme.typography.fontWeightBold,
      color: theme.palette.text.primary
    },
    icon: {
      fontSize: theme.mixins.pxToRem(18),
      marginRight: theme.spacing(2),
      marginTop: theme.spacing(0.5),
      color: theme.palette.text.secondary
    },
    address: {
      fontSize: theme.mixins.pxToRem(13),
      color: theme.palette.text.secondary,
      marginRight: theme.spacing(2)
    },
    media: {},
    active: {
      color: theme.palette.primary.main,
      '& $icon': {
        color: theme.palette.primary.main
      }
    }
  });

type Props = {
  name: string;
  active?: boolean;
  icon?: string;
  address?: string;
  classes: Record<
    'root' | 'title' | 'address' | 'info' | 'media' | 'icon' | 'active',
    string
  >;
  onClick?: () => void;
};

function ResultItem({ name, address, classes, active, onClick }: Props) {
  return (
    <div
      role="button"
      aria-label="select place"
      data-testid="suggestion"
      className={classNames([classes.root, active && classes.active])}
      onClick={onClick}
    >
      <LineIcon icon="ico-checkin-o" className={classes.icon} />
      <div className={classes.info}>
        <span className={classNames([classes.title, active && classes.active])}>
          {name}
        </span>
        {address ? (
          <div
            className={classNames([classes.address, active && classes.active])}
            children={address}
          />
        ) : null}
      </div>
    </div>
  );
}

export default withStyles(styles)(ResultItem);
