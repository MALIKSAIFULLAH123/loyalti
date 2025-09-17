import { LineIcon } from '@metafox/ui';
import { IconButton, Theme, Tooltip } from '@mui/material';
import { createStyles, withStyles } from '@mui/styles';
import React from 'react';

const styleMap = color => {
  if (!color) return {};

  const mapping = {
    inherit: {
      color: theme => theme.palette.text.secondary,
      backgroundColor: theme =>
        (theme.palette.mode === 'dark'
          ? 'rgba(85,85,85,0.9)'
          : 'rgba(255,255,255,0.9)'),
      '&:hover': {
        backgroundColor: theme =>
          (theme.palette.mode === 'dark'
            ? 'rgba(85,85,85,0.96)'
            : 'rgba(255,255,255,0.96)'),
        color: theme => theme.palette.text.primary
      }
    },
    primary: {
      color: theme => theme.palette.common.white,
      backgroundColor: 'rgba(0,0,0,0.7)',
      '&:hover': {
        backgroundColor: 'rgba(0,0,0,0.8)',
        color: theme => theme.palette.common.white
      }
    }
  };

  return mapping[color] || {};
};

const styles = (theme: Theme) =>
  createStyles({
    root: {
      boxShadow: theme.shadows[1]
    },
    sizeSmall: {
      fontSize: '13px',
      padding: 8
    }
  });

function MyIconButton({ title, classes, icon, ...rest }) {
  return (
    <Tooltip title={title}>
      <IconButton classes={classes} sx={styleMap(rest?.color)} {...rest}>
        <LineIcon icon={icon} />
      </IconButton>
    </Tooltip>
  );
}

export default withStyles(styles)(MyIconButton);
