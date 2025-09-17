import { BlockViewProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { styled } from '@mui/material';
import React, { useState } from 'react';

const WrapperHeader = styled('div')(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between'
}));

const Wrapper = styled('div')(({ theme }) => ({
  marginTop: theme.spacing(2)
}));

export interface Props extends BlockViewProps {}

export default function GeneralSettings({ title, blockProps }: Props) {
  const { ListView } = useGlobal();
  const [dataSource] = useState({
    apiUrl: '/account/blocked-user'
  });
  const [query] = useState('');

  const pagingId = 'pagination.blockedUsers';
  // const SearchBox = jsxBackend.get('ui.searchBox');

  // const onQueryChange = value => {
  //   setQuery(value);
  // };

  return (
    <Block>
      <WrapperHeader>
        <BlockHeader title={title} />
        {/* {jsxBackend.render({
          component: SearchBox,
          props: { onQueryChange, value: query }
        })} */}
      </WrapperHeader>
      <BlockContent>
        <Wrapper>
          <ListView
            acceptQuerySearch
            query={query}
            dataSource={dataSource}
            pagingId={pagingId}
            canLoadMore={false}
            clearDataOnUnMount
            gridContainerProps={{ spacing: 2 }}
            gridLayout="Feed - Main Card"
            itemLayout="Friend - Cards"
            itemView="blocked_user.itemView.smallCard"
          />
        </Wrapper>
      </BlockContent>
    </Block>
  );
}
