/**
 * @type: block
 * name: advertise.block.sponsorFeed
 */

import { APP_NAME, RESOURCE_SPONSOR } from '@metafox/advertise/constants';
import { RemoteFormBuilder } from '@metafox/form';
import {
  BlockViewProps,
  createBlock,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import React from 'react';

function SponsorFeed() {
  const { jsxBackend, compactUrl, usePageParams } = useGlobal();
  const dataSource = useResourceAction(
    APP_NAME,
    RESOURCE_SPONSOR,
    'addFeedItem'
  );
  const params = usePageParams();

  const EmptyPage = jsxBackend.get('core.block.error404');

  if (!dataSource) return React.createElement(EmptyPage);

  return (
    <Block testid="SponsorFeed">
      <BlockContent>
        <RemoteFormBuilder
          dataSource={{
            apiUrl: compactUrl(dataSource.apiUrl, params)
          }}
          navigationConfirmWhenDirty={false}
        />
      </BlockContent>
    </Block>
  );
}

export default createBlock<BlockViewProps>({
  extendBlock: SponsorFeed
});
