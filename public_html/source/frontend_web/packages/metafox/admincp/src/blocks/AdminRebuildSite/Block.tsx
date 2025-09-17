/**
 * @type: block
 * name: core.block.AdminRebuildSite
 * title: AdminCP - Rebuild Sites
 * bundle: admincp
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import React from 'react';

export function AdminRebuildSite() {
  return (
    <Block>
      <BlockHeader title="Rebuild Site" />
      <BlockContent>This class is deprecated</BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: AdminRebuildSite,
  defaults: {
    blockLayout: 'Blocker'
  }
});
