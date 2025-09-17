import {
  BlockViewProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import React from 'react';
import { NOTIFICATION, NOTIFICATION_SETTING } from '@metafox/user/constant';

export interface Props extends BlockViewProps {
  initialValues: any;
}

export default function Base({ title, initialValues, blockProps }: Props) {
  const { usePageParams, i18n } = useGlobal();
  const pageParams = usePageParams();

  const dataSource = useResourceAction(
    NOTIFICATION,
    NOTIFICATION_SETTING,
    'notificationSettingsByChannel'
  );

  return (
    <Block testid="setting-channel">
      <BlockHeader>
        <BlockTitle>
          {i18n.formatMessage(
            { id: 'channel_notifications' },
            { channel: pageParams?.channel }
          )}
        </BlockTitle>
      </BlockHeader>
      <BlockContent>
        <SmartFormBuilder
          noTitle
          dataSource={dataSource}
          pageParams={pageParams}
        />
      </BlockContent>
    </Block>
  );
}
