/**
 * @type: block
 * name: core.block.AppBarMini
 * title: Site App Bar Mini
 * keywords: general
 * experiment: true
 */
import { createBlock } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import AppBarMini from './AppBarMini';
import React from 'react';

const Base = () => {
  return (
    <Block>
      <BlockContent>
        <AppBarMini />
      </BlockContent>
    </Block>
  );
};

export default createBlock({
  extendBlock: Base,
  defaults: {}
});
