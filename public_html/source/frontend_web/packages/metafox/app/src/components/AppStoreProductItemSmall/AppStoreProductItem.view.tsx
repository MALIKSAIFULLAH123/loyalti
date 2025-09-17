/**
 * @type: itemView
 * name: app_store_product.itemView.smallCard
 * chunkName: app_store_product
 */
import { StoreProductItemShape } from '@metafox/core/types';
import useGetItem from '@metafox/framework/hooks/useGetItem';
import AppStoreProductItem from './AppStoreProductItem';
import React from 'react';

const ItemView = props => {
  const item = useGetItem<StoreProductItemShape>(props.identity);

  return <AppStoreProductItem item={item} {...props} />;
};

export default ItemView;
