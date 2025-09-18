/**
 * @type: block
 * name: marketplace.block.listingBlock
 * title: Marketplace Listings
 * keywords: marketplace
 * description: Display listings marketplace
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

const MarketplaceListingBlock = createBlock<ListViewBlockProps>({
  name: 'MarketplaceListingBlock',
  extendBlock: 'core.block.listview',
  overrides: {
    contentType: 'marketplace',
    dataSource: { apiUrl: '/marketplace' }
  }
});

export default MarketplaceListingBlock;
