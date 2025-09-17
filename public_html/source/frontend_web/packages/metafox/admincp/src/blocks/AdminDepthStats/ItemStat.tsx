import { Box, styled } from '@mui/material';
import React from 'react';
import TabContent from './TabContent';
import ItemRow from './ItemRow';

const Title = styled('h3', { slot: 'Title' })(({ theme }) => ({
  color: theme.palette.grey[600],
  marginTop: 0,
  marginBottom: theme.spacing(1),
  fontSize: 16
}));

const ItemWrapper = styled(Box, { slot: 'ItemWrapper' })(({ theme }) => ({
  paddingTop: theme.spacing(2),
  '&:first-of-type': {
    paddingTop: 0
  }
}));

type Props = {
  item;
  tabId: number;
  changeTab: () => void;
  limit?: number;
};

export default function ItemStat({ item, tabId, changeTab, limit }: Props) {
  if (!item) return null;

  const { title, items = [], tabs = {} } = item;
  const tabsData = Object.values(tabs);

  return (
    <ItemWrapper>
      <Title> {title} </Title>
      {items
        ? items?.map((item, index) => <ItemRow key={`k${index}`} item={item} />)
        : null}
      {tabsData?.length ? (
        <TabContent
          limit={limit}
          tabs={tabsData}
          tabId={tabId}
          changeTab={changeTab}
        />
      ) : null}
    </ItemWrapper>
  );
}
