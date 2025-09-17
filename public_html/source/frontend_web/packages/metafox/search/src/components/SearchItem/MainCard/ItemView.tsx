import { useGlobal } from '@metafox/framework';
import { SearchItemProps } from '@metafox/search/types';
import * as React from 'react';

export function SearchItemMainCard({
  item,
  wrapAs,
  wrapProps,
  ...props
}: SearchItemProps) {
  const { jsxBackend } = useGlobal();

  if (!item) return null;

  const { item_type, embed_object } = item;
  const Item = jsxBackend.get(`${item_type}.itemView.mainCard`);

  return React.createElement(Item, {
    wrapAs,
    wrapProps,
    ...props,
    identity: embed_object
  });
}

export default SearchItemMainCard;
