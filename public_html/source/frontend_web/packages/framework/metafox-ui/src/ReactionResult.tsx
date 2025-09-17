import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';
import clsx from 'clsx';
import * as React from 'react';

type ReactionItemProps = {
  resource_name: string;
  module_name: string;
  title: string;
  icon: string;
  color: string;
  ordering: number;
  is_default: boolean;
  id: number;
};

type Props = {
  reactions?: Array<ReactionItemProps>;
  statistic?: Record<string, number>;
  size?: 'sm' | 'md';
};

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'inline-flex',
        alignItems: 'center'
      },
      listReaction: {
        display: 'inline-flex'
      },
      md: {
        '& $itemReaction': {
          width: '24px',
          height: '24px',
          '& + $itemReaction': {
            marginLeft: '-4px'
          }
        },
        '& $listReaction': {
          marginRight: theme.spacing(1.5)
        }
      },
      sm: {
        '& $itemReaction': {
          width: '16px',
          height: '16px',
          '& + $itemReaction': {
            marginLeft: '-3px'
          }
        },
        '& $listReaction': {
          marginRight: theme.spacing(1)
        }
      },
      itemReaction: {
        display: 'inline-flex',
        boxSizing: 'content-box',
        borderRadius: '100%',
        lineHeight: 0,
        width: '100%',
        height: '100%',
        '& img': {
          width: '100%',
          height: '100%',
          borderRadius: '100%'
        }
      },
      totalReaction: {
        fontSize: theme.mixins.pxToRem(13),
        color: theme.palette.text.secondary
      }
    }),
  { name: 'MuiReactionResult' }
);

const ReactionResult = (props: Props) => {
  const { reactions, statistic, size = 'md' } = props;
  const { total_like } = statistic;
  const classes = useStyles();

  if (!reactions) return null;

  return (
    <div className={clsx(classes.root, classes[size])}>
      <div className={classes.listReaction}>
        {reactions &&
          reactions.map((item, index) => (
            <span
              className={classes.itemReaction}
              key={index.toString()}
              style={{ zIndex: index + 1 }}
            >
              <img src={item.icon} alt={item.title} />
            </span>
          ))}
      </div>
      <span className={classes.totalReaction}>{total_like}</span>
    </div>
  );
};

export default ReactionResult;
