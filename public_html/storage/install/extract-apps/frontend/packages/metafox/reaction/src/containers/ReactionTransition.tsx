import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';
import React from 'react';

export default function ReactionTransition({ children, isActive }) {
  const useStyles = makeStyles((theme: Theme) =>
    createStyles({
      root: {},
      reactionWrapper: {
        transform: 'translateY(32px)',
        transition: 'all 225ms cubic-bezier(0, 0, 0.2, 1)',
        opacity: 0
      },
      reactionTransition: {
        transform: 'translateY(0)',
        opacity: 1
      }
    })
  );

  const classes = useStyles();
  const [active, setActive] = React.useState<Boolean>(false);

  React.useEffect(() => {
    setActive(isActive);
  }, [isActive]);

  return (
    <div
      className={`${classes.reactionWrapper} ${
        active ? classes.reactionTransition : ''
      }`}
    >
      {children}
    </div>
  );
}
