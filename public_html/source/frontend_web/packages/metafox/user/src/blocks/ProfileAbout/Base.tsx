import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Skeleton } from '@mui/material';
import { get } from 'lodash';
import React from 'react';

export interface Props extends BlockViewProps {
  sectionName: string;
}

export default function UserProfileAboutBlock({
  title,
  blockProps,
  sectionName = 'basic_info',
  item
}: Props & { item: any }) {
  const { useFetchDetail, usePageParams, jsxBackend } = useGlobal();
  const pageParams = usePageParams();

  const [data, loading] = useFetchDetail({
    dataSource: {
      apiUrl: `/user/info/${pageParams.id}`
    },
    pageParams
  });

  if (!item?.profile_settings?.profile_view_profile) return null;

  if (loading) {
    return (
      <Block testid={'detailview profile detail about'}>
        <BlockHeader title={title} />
        <BlockContent>
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
        </BlockContent>
      </Block>
    );
  }

  const section = get(data, `sections.${sectionName}`);

  if (!section) {
    const NoContentBlock = jsxBackend.get('core.block.no_content');

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <NoContentBlock />
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block testid={'detailview profile detail about'}>
      <BlockHeader title={title || section.label} />
      <BlockContent>
        {jsxBackend.render({
          component: section.component ?? 'layout.section.list_info',
          props: { section }
        })}
      </BlockContent>
    </Block>
  );
}
