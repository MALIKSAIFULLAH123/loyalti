/**
 * @type: block
 * name: photo_album.block.profileViewAlbumDetail
 * title: Profile Photo Album Detail
 * keywords: photo, profile
 * description: Display photo album detail profile
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '@metafox/photo/hocs/connectPhotoAlbum';
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
    blockProps: {
      variant: 'plained',
      titleComponent: 'h2',
      titleVariant: 'subtitle1',
      titleColor: 'textPrimary',
      noFooter: true,
      noHeader: true,
      blockStyle: {
        sx: {
          pt: 0,
          mt: 2
        }
      },
      contentStyle: {},
      headerStyle: {},
      footerStyle: {}
    }
  }
});
