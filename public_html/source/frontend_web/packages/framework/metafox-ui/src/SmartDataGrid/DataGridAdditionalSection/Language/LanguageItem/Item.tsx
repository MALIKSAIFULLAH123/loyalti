/**
 * @type: itemView
 * name: language.itemView.recommendCard
 */
import Base from './Base';
import React from 'react';
import { useGetItem } from '@metafox/framework';

const ItemView = props => {
  const item = useGetItem(props?.identity);

  if (!item) return null;

  return <Base item={item} {...props} />;
};

export default ItemView;
