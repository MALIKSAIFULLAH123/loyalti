import React from 'react';
import { useGlobal, useResourceAction } from '@metafox/framework';
import {
  APP_NAME,
  LIMIT_SITE_BLOCK_DEFAULT
} from '@metafox/advertise/constants';
import { compactData } from '@metafox/utils';

export interface Props {
  [key: string]: any;
}

function Base(props: Props) {
  const { slotName = 'main', placement_id, blockLayout } = props;
  const { jsxBackend, getSetting } = useGlobal();

  const setting: any = getSetting('advertise');
  const dataSource = useResourceAction(APP_NAME, APP_NAME, 'showAll');

  if (!setting || !dataSource) return null;

  const displayLimit = ['main', 'top'].includes(slotName)
    ? LIMIT_SITE_BLOCK_DEFAULT
    : setting?.maximum_number_of_advertises_on_side_block;

  const ListView = jsxBackend.get('core.block.listview');

  return (
    <ListView
      displayLimit={displayLimit}
      dataSource={{
        apiMethod: dataSource.apiMethod || 'get',
        apiUrl: dataSource.apiUrl,
        apiParams: compactData(dataSource.apiParams, {
          placement_id,
          location: slotName
        })
      }}
      preventRefreshWhenEmpty
      blockLayout={blockLayout}
      itemLayout="Advertise List Item"
      gridLayout="Advertise List Item"
      itemProps={{ slotName }}
      emptyPage="hide"
      errorPage="hide"
      itemView="advertise.block.advertisesBlockItem"
    />
  );
}

export default Base;
