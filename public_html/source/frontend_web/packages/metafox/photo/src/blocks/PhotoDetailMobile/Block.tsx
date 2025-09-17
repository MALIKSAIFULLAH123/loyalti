/**
 * @type: block
 * name: photo.block.photoViewMobile
 * title: Photo Detail
 * keywords: photo
 * description: Display photo detail on mobile
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/photo/hocs/connectPhoto';
import Base, { Props } from './Base';

const Enhance = connectSubject(
  connectItemView(Base, actionCreators, {
    tags: true,
    categories: true
  })
);

export default createBlock<Props>({
  extendBlock: Enhance,
  defaults: {
    placeholder: 'Search',
    blockLayout: 'Photo Page View',
    blockProps: {
      variant: 'plained',
      titleComponent: 'h2',
      titleVariant: 'subtitle1',
      titleColor: 'textPrimary',
      noFooter: true,
      noHeader: true
    }
  }
});
