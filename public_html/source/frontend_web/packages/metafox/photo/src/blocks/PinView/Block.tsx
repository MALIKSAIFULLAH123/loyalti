/**
 * @type: block
 * name: photo.block.pinView
 * title: Photos
 * keywords: photo
 * description: Display photo items
 * thumbnail:
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'PhotoListingBlock',
  extendBlock: 'core.listing.pinView',
  defaults: {
    title: 'Photos',
    itemView: 'photo.itemView.pinCard',
    blockProps: {
      blockStyle: {
        pr: 2,
        pl: 2,
        pb: 2
      }
    },
    gridVariant: 'pinView',
    canLoadMore: true,
    startItemView: [
      {
        as: 'photo_album.itemView.AddPhotoAlbumDetail',
        showWhen: ['truthy', 'item.extra.can_upload_media']
      }
    ]
  }
});
