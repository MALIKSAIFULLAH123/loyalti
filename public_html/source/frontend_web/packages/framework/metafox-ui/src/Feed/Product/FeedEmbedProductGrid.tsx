/**
 * @type: ui
 * name: feedArticle.view.grid.embedItem
 */
import { ImageRatio, ItemShape } from '@metafox/ui';
import * as React from 'react';
import FeedEmbedProductTemplate from './FeedEmbedProductTemplate';

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

const FeedArticleGrid = (props: Props) => (
  <FeedEmbedProductTemplate
    {...props}
    variant={'grid'}
    widthImage={'100%'}
    mediaRatio={'169'}
  />
);

export default FeedArticleGrid;
