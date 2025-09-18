import { RemoteFormBuilder } from '@metafox/form';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useSession
} from '@metafox/framework';
import { APP_GROUP, RESOURCE_GROUP } from '@metafox/group';
import {
  Block,
  BlockContent,
  BlockDesc,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
import { Card, styled } from '@mui/material';
import React from 'react';

const CardItem = styled(Card, {
  name: 'LayoutSlot',
  slot: 'rootBorder',
  overridesResolver(props, styles) {
    return [styles.rootBorder];
  }
 })(({ theme }) => ({
  boxShadow: 'none',
  paddingLeft: theme.spacing(2),
  paddingRight: theme.spacing(2)
 }));

export default function ModerationRights({
  title,
  blockProps
}: BlockViewProps) {
  const { loggedIn } = useSession();
  const pageParams = usePageParams();
  const { i18n } = useGlobal();

  const dataSource = useResourceAction(
    APP_GROUP,
    RESOURCE_GROUP,
    'viewModerationRight'
  );

  if (!loggedIn) {
    return null;
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockDesc variant="body2" paragraph>
        {i18n.formatMessage({ id: 'moderation_right_description' })}
      </BlockDesc>
      <BlockContent>
        <CardItem>
          <RemoteFormBuilder
            noHeader
            dataSource={dataSource}
            pageParams={pageParams}
          />
        </CardItem>
      </BlockContent>
    </Block>
  );
}
