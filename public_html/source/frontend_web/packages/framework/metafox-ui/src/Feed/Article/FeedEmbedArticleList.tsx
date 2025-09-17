/**
 * @type: ui
 * name: feedProduct.view.list.embedItem
 */
import { ImageRatio, ItemShape } from '@metafox/ui';
import * as React from 'react';
import FeedEmbedArticleTemplate from './FeedEmbedArticleTemplate';

type Props = {
  title?: string;
  description?: string;
  link?: string;
  host?: string;
  image?: string;
  mediaRatio?: ImageRatio;
  price?: string;
  displayStatistic?: string;
  maxLinesTitle?: number;
  maxLinesDescription?: number;
  highlightSubInfo?: string;
  variant?: 'grid' | 'list';
} & ItemShape;

const FeedArticleList = (props: Props) => (
  <FeedEmbedArticleTemplate {...props} variant={'list'} />
);

export default FeedArticleList;
