import { BlockViewProps, useResourceAction } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import * as React from 'react';
import { AppState } from '../../types';
import { SmartFormBuilder } from '@metafox/form';

export type Props = BlockViewProps & AppState['emailNotificationSettings'];

export default function GeneralSettings({ title }: Props) {
  const dataSource = useResourceAction(
    'notification',
    'notification_setting',
    'getSMSNotificationSettingsForm'
  );

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <SmartFormBuilder noTitle dataSource={dataSource} />
      </BlockContent>
    </Block>
  );
}
