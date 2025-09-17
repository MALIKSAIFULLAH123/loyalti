import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';
import React from 'react';

const useStyles = makeStyles((theme: Theme) =>
  createStyles({
    root: {},
    mentionSuggestionsWrapper: {
      position: 'absolute',
      backgroundColor: theme.mixins.backgroundColor('paper'),
      borderRadius: 4
    },
    mentionOptionWrapper: {
      display: 'flex',
      alignItems: 'center',
      padding: theme.spacing(0.5, 0),
      cursor: 'pointer'
    },
    avatar: {
      width: 32,
      height: 32,
      borderRadius: '50%'
    },
    name: {
      marginLeft: theme.spacing(1),
      color: theme.palette.text.primary,
      fontSize: theme.mixins.pxToRem(13)
    }
  })
);

export default function FriendSuggestionEntry({ user, ...otherProps }) {
  const classes = useStyles();

  return (
    <div className={classes.root} {...otherProps}>
      <div className={classes.mentionOptionWrapper}>
        <img src={user.avatar} alt={user.name} className={classes.avatar} />
        <span className={classes.name}>{user.name}</span>
      </div>
    </div>
  );
}
