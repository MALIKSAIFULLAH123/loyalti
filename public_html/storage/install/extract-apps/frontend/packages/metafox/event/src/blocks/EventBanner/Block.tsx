/**
 * @type: block
 * name: event.block.eventBannerView
 * title: Event Detail Banner
 * keywords: event
 * description: Display event detail banner.
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/event/hocs/connectEventDetail';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators));

export default createBlock<any>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search',
    blockProps: {
      variant: 'plained',
      titleComponent: 'h2',
      titleVariant: 'subtitle1',
      titleColor: 'textPrimary',
      noFooter: true,
      noHeader: true,
      blockStyle: {},
      contentStyle: {
        borderRadius: 'base',
        pt: 0,
        pb: 0
      },
      headerStyle: {},
      footerStyle: {}
    }
  }
});
