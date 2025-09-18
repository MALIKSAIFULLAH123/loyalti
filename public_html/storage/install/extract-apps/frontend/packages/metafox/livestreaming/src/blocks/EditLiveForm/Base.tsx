import {
  BlockViewProps,
  useResourceAction,
  useGlobal
} from '@metafox/framework';
import { RemoteFormBuilder } from '@metafox/form';
import { Block, BlockContent } from '@metafox/layout';
import { compactUrl } from '@metafox/utils';
import * as React from 'react';
import { AppResourceAction } from '@metafox/framework/Manager';
import {
  APP_LIVESTREAM,
  RESOURCE_LIVE_VIDEO,
  LivestreamItemShape
} from '@metafox/livestreaming';

export type Props = BlockViewProps & {
  item: LivestreamItemShape;
  data: {
    apiMethod: 'get' | 'post' | 'put' | 'patch' | 'delete';
    apiUrl: string;
  };
};

export default function EditLiveStream({ item }: Props) {
  const { useGetItem, useSession } = useGlobal();
  const dataSource: AppResourceAction = useResourceAction(
    APP_LIVESTREAM,
    RESOURCE_LIVE_VIDEO,
    'editLivestream'
  );

  const { user: authUser } = useSession();

  const { user: userIdentity, is_streaming } = item || {};
  const owner = useGetItem(userIdentity);
  const isOwner = authUser?.id == owner?.id;

  if (!item || !dataSource || !is_streaming || !isOwner) return null;

  const id = item?.id;

  return (
    <Block>
      <BlockContent>
        <RemoteFormBuilder
          dataSource={{
            apiMethod: dataSource.apiMethod,
            apiUrl: compactUrl(dataSource.apiUrl, { id })
          }}
          navigationConfirmWhenDirty={false}
        />
      </BlockContent>
    </Block>
  );
}
