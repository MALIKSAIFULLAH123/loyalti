/**
 * @type: block
 * name: user.block.mfa
 */

import { SmartFormBuilder } from '@metafox/form';
import {
  BlockViewProps,
  createBlock,
  useGlobal,
  useLocation,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import React from 'react';
import qs from 'query-string';
import { isEmpty } from 'lodash';

function MfaBlock({ title }) {
  const { jsxBackend } = useGlobal();
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const dataSource = useResourceAction('mfa', 'user_auth', 'authForm');

  const EmptyPage = jsxBackend.get('core.block.error404');

  if (isEmpty(searchParams) || !dataSource)
    return React.createElement(EmptyPage);

  return (
    <Block testid="blockMFA">
      <BlockContent>
        <SmartFormBuilder
          dataSource={{
            ...dataSource,
            apiParams: { ...dataSource.apiParams, ...searchParams }
          }}
          navigationConfirmWhenDirty={false}
        />
      </BlockContent>
    </Block>
  );
}

export default createBlock<BlockViewProps>({
  extendBlock: MfaBlock,
  defaults: {
    title: 'authentication',
    blockLayout: 'Resend Email - Contained'
  }
});
