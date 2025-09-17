/**
 * @type: service
 * name: CommentReaction
 */
import { styled } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';

const name = 'CommentReaction';

const Item = styled('div', { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexGrow: 1,
  flexFlow: 'column',
  overflow: 'hidden',
  justifyContent: 'space-between'
}));
const ItemOuter = styled('div', { name, slot: 'itemOuter' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-around',
  alignItems: 'center',
  minHeight: theme.spacing(5),
  '& button:hover': {
    backgroundColor: theme.palette.action.selected
  },
  [theme.breakpoints.down('sm')]: {
    '& button:hover': {
      backgroundColor: 'transparent'
    },
    '& button': {
      flex: 1
    }
  }
}));

export default function CommentReaction({ children }) {
  if (children && isEmpty(children.filter(Boolean))) return null;

  return (
    <Item>
      <ItemOuter>{children}</ItemOuter>
    </Item>
  );
}
