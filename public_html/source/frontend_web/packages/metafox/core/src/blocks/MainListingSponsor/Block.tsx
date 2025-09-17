/**
 * @type: block
 * name: core.block.mainListingSponsor
 * title: Main Listing Sponsor
 * keywords: general
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  extendBlock: 'core.block.listview',
  defaults: {
    isTrackingSponsor: 1,
    showWhen: ['truthy', 'acl.advertise.advertise_sponsor.view']
  }
});
