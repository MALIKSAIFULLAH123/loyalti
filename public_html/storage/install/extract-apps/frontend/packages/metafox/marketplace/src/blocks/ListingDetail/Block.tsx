/**
 * @type: block
 * name: listing.block.listingView
 * title: Listing Detail
 * keywords: photo
 * description: Display listing detail
 */

import { connectSubject, createBlock } from '@metafox/framework';
import { MarketplaceDetailViewProps } from '@metafox/marketplace/types';
import {
  actionCreators,
  connectItemView
} from '../../hocs/connectMarketplaceItem';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<MarketplaceDetailViewProps>({
  extendBlock: Enhance
});
