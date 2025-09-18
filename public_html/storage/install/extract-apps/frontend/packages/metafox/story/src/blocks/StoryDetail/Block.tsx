/**
 * @type: block
 * name: story.block.storyDetailView
 * title: Story Detail
 * keywords: story
 * description: Display story detail
 * experiment: true
 */

import { connectSubject, createBlock } from '@metafox/framework';
import Base from './Base';

const Enhance = connectSubject(Base);

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
      blockStyle: {
        height: '100%'
      },
      contentStyle: {
        borderRadius: 'base',
        height: '100%'
      },
      headerStyle: {},
      footerStyle: {}
    }
  }
});
