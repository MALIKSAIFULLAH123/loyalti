/* eslint-disable react/jsx-no-useless-fragment */
import {
  AppUIConfig,
  CategoryBlockProps,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import * as React from 'react';
import ItemView from './ItemView';
import checkActiveMenu from './checkActiveMenu';

export type Props = {
  sidebarCategory: AppUIConfig['sidebarCategory'];
  appName?: string;
} & CategoryBlockProps;

export default function SideCategoryBlock({
  title: title2,
  sidebarCategory,
  blockProps,
  appName: appNameProp
}: Props) {
  const { useFetchItems, usePageParams } = useGlobal();
  const appName = sidebarCategory?.appName || appNameProp;
  const config = useResourceAction(
    appName,
    sidebarCategory?.resourceName || `${appName}_category`,
    sidebarCategory?.actionName || 'viewAll'
  );

  const [items] = useFetchItems({
    dataSource: config || sidebarCategory?.dataSource,
    data: [],
    cache: true,
    normalize: false
  });

  const pageParams = usePageParams();
  const { category } = pageParams || {};

  if (!sidebarCategory) return null;

  const { title } = sidebarCategory;

  return (
    <>
      {items.length ? (
        <Block testid="blockSidebarCategory">
          <BlockHeader title={title || title2} />
          <BlockContent>
            {items.map(item => (
              <ItemView
                id={item.id}
                name={item.name}
                resource_name={item.resource_name}
                key={item.id.toString()}
                active={
                  category === item.id.toString() ||
                  checkActiveMenu(pageParams, item)
                }
                subs={item.subs}
                link={item?.link || item?.url}
              />
            ))}
          </BlockContent>
        </Block>
      ) : null}
    </>
  );
}
