/**
 * @type: block
 * name: photo.block.photoView
 * title: Photo Detail
 * keywords: photo
 * description: Display photo detail
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
    blockLayout: 'Photo Page View'
  }
});
