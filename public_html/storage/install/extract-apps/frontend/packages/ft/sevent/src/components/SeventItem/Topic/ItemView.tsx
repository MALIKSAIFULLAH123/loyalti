/**
 * @type: itemView
 * name: sevent.itemView.topic
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
import { Link, connectItemView } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

export function Topic({
  item
}) {
  const { id, label } = item;
  const TagItem = styled('div', { 
    name: 'TagItem', 
    slot: 'tagItem',
    overridesResolver(props, styles) {
     return [styles.tagItem];
   }
  })(({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    fontWeight: theme.typography.fontWeightBold,
    borderRadius: theme.shape.borderRadius / 2,
    width: '25%',
    marginTop: theme.spacing(2),
    padding: '8px',
    paddingLeft: '20px',
    display: 'block',
    fontSize: '14px',
    [theme.breakpoints.down('sm')]: {
      width: '50%',
      paddingLeft: '16px'
    }
  }));

  return (
    <>
      {id == 0 ? (
        <TagItem key={id}>
          <Link to={'/sevent/all'}>
            {label}
          </Link>
        </TagItem>
      ) : (
        <TagItem key={id}>
          <Link to={`/sevent/all?category_id=${id}`}>
            {label}
          </Link>
        </TagItem>
      )}
    </>
  );
}

export default connectItemView(Topic, actionCreators, {});