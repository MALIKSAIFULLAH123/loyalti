import * as React from 'react';
import { EmbedFeedInFeedItemProps } from '../types';
import ItemView from './EmbedFeed';

export default function EmbedFeedInFeedItemView(
  props: EmbedFeedInFeedItemProps
) {
  return <ItemView {...props} />;
}
