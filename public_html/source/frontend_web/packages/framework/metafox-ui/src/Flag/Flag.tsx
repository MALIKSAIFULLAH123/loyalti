import { useGlobal } from '@metafox/framework';
import clsx from 'clsx';
import React from 'react';
import LineIcon from '../LineIcon';
import useStyles from './Flag.styles';
import { styled } from '@mui/material';
import { ItemShape } from '../types';

export interface FlagProps {
  type?:
    | 'is_featured'
    | 'is_sponsor'
    | 'is_pending'
    | 'is_expires'
    | 'is_denied';
  color?: 'primary' | 'white' | 'yellow';
  variant?: 'feedView' | 'itemView' | 'detailView' | 'text';
  className?: string;
  'data-testid': string;
  value?: boolean;
  text?: string;
  showTitleMobile?: boolean;
  item?: ItemShape;
}

const TitleFlag = styled('span', {
  slot: 'TitleFlag',
  shouldForwardProp: props => props !== 'showTitleMobile'
})<{ showTitleMobile?: boolean }>(({ theme, showTitleMobile }) => ({
  [theme.breakpoints.down('sm')]: {
    ...(!showTitleMobile && {
      display: 'none'
    })
  }
}));

function Flag({
  type,
  color = 'primary',
  variant = 'feedView',
  'data-testid': testid,
  value,
  className,
  text,
  showTitleMobile
}: FlagProps) {
  const classes = useStyles();
  const { i18n } = useGlobal();

  if (!value) return null;

  let label = {
    icon: '',
    title: ''
  };
  switch (type) {
    case 'is_featured':
      label = {
        icon: 'ico-diamond',
        title: 'featured'
      };
      break;
    case 'is_sponsor':
      label = {
        icon: 'ico-sponsor',
        title: 'sponsored'
      };
      break;
    case 'is_pending':
      label = {
        icon: 'ico-clock-o',
        title: 'pending'
      };
      break;
    case 'is_expires':
      label = {
        icon: '',
        title: text
      };
      break;
    case 'is_denied':
      label = {
        icon: '',
        title: text
      };
      break;
    default:
  }

  return (
    <span className={clsx(classes.root, className, classes[variant])}>
      <span className={clsx(classes.item, classes[color])} data-testid={testid}>
        <span className={classes.icon}>
          <LineIcon icon={label.icon} />
        </span>
        <TitleFlag
          showTitleMobile={showTitleMobile}
          className={clsx(classes.text, classes[type])}
        >
          {i18n.formatMessage({ id: label.title })}
        </TitleFlag>
      </span>
    </span>
  );
}

export default Flag;
