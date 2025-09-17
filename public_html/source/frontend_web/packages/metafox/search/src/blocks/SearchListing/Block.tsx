/**
 * @type: block
 * name: search.block.SearchResultListings
 */

import { createBlock, ListViewBlockProps } from '@metafox/framework';
import Base from './Base';

const GlobalSearchResultBlock = createBlock<ListViewBlockProps>({
  name: 'GlobalSearchResultBlock',
  extendBlock: Base,
  overrides: {
    contentType: 'search',
    blockLayout: 'Search Main Lists'
  }
});

export default GlobalSearchResultBlock;
