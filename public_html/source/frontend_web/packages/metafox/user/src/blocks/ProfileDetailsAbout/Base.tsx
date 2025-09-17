import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Skeleton } from '@mui/material';
import React from 'react';

export interface Props extends BlockViewProps {}

const MS_1_HOUR = 36e5;

export default function UserCustomProfile({ title, ...rest }: Props) {
  const { useFetchDetail, usePageParams, jsxBackend, useGetItem } = useGlobal();
  const pageParams = usePageParams();
  const item = useGetItem(pageParams?.identity);

  const cacheKey = `user-about-${pageParams.id}-${
    item?._updateProfileInfo || ''
  }`;

  const [data, loading, , msgInfo] = useFetchDetail({
    dataSource: {
      apiUrl: `/user/info/${pageParams.id}`
    },
    pageParams,
    cachePrefix: 'user-profile',
    cacheKey,
    ttl: MS_1_HOUR,
    forceReload: false
  });

  const NoContentBlock = jsxBackend.get('core.block.no_content');

  if (loading) {
    return (
      <Block {...rest} testid={'detailview profile detail about'}>
        <BlockHeader title={title} />
        <BlockContent>
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
          <Skeleton height={20} width="100%" />
        </BlockContent>
      </Block>
    );
  }

  const sections = Object.values(data?.sections);

  if (!sections.length) {
    return (
      <Block {...rest}>
        <BlockHeader title={title} />
        <BlockContent>
          <NoContentBlock title={msgInfo || undefined} />
        </BlockContent>
      </Block>
    );
  }

  return (
    <>
      {sections.map((section, index) => (
        <Block key={`i${index}`} {...rest}>
          <BlockHeader title={section?.label} />
          <BlockContent>
            {jsxBackend.render({
              component: section.component ?? 'layout.section.list_info',
              props: { section }
            })}
          </BlockContent>
        </Block>
      ))}
    </>
  );
}
