import { useGlobal, useResourceAction } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import * as React from 'react';
import {
  APP_SUBSCRIPTION,
  RESOURCE_SUBSCRIPTION_PACKAGE
} from '@metafox/subscription';
import { Box } from '@mui/material';

const ItemVIew = ({
  title,
  itemView,
  emptyPage,
  emptyPageProps,
  itemProps,
  gridItemProps,
  gridContainerProps
}) => {
  const { ListView, jsxBackend, getSetting, useSession, redirectTo, i18n } =
    useGlobal();
  const { user: authUser } = useSession();
  const enablePackageSubscription = getSetting(
    'subscription.enable_subscription_packages'
  );

  const dataSource = useResourceAction(
    APP_SUBSCRIPTION,
    RESOURCE_SUBSCRIPTION_PACKAGE,
    'viewAll'
  );

  if (!enablePackageSubscription && authUser?.extra.can_view_subscriptions) {
    redirectTo('/subscription');

    return null;
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          dataSource={dataSource}
          gridItemProps={gridItemProps}
          itemProps={itemProps}
          itemView={itemView}
          limitItemsLoadSmooth={3}
          emptyPage={emptyPage}
          emptyPageProps={emptyPageProps}
        />
        <Box mt={2}>
          {jsxBackend.render({
            component: 'subscription_comparison.block.comparisonBlock',
            props: {
              title: i18n.formatMessage({ id: 'packages_comparison' })
            }
          })}
        </Box>
      </BlockContent>
    </Block>
  );
};

const LoadingSkeleton = () => {
  return <div></div>;
};

ItemVIew.LoadingSkeleton = LoadingSkeleton;

export default ItemVIew;
