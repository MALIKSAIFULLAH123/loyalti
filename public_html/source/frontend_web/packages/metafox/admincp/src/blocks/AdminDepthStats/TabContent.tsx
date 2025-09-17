import { styled, Tabs, Tab } from '@mui/material';
import React from 'react';
import ItemRow from './ItemRow';

const TabItem = styled(Tab, { slot: 'Tab' })(({ theme }) => ({
  padding: '0 !important',
  minHeight: '32px',
  marginRight: theme.spacing(2),
  fontSize: '13px',
  color: theme.palette.primary.main,
  minWidth: 'auto !important',
  '&.Mui-selected': {
    fontWeight: 'bold'
  }
}));

const TabWrapper = styled(Tabs, { slot: 'TabWrapper' })(({ theme }) => ({
  minHeight: '32px'
}));

type Props = {
  limit?: number;
  tabs: Array<any>;
};

export default function TabContent({ limit, tabs, tabId, changeTab }: Props) {
  // const [tabId, setTabId] = React.useState<number>(0);

  // const changeTab = (_, value) => {
  //   setTabId(value);
  // };

  const dataTab = limit
    ? tabs[tabId]?.items.slice(0, limit)
    : tabs[tabId]?.items;

  return (
    <>
      <TabWrapper value={tabId} onChange={changeTab} sx={{ mb: 1 }}>
        {tabs.map((tab, index) => (
          <TabItem key={`k${index}`} disableRipple label={tab.title} />
        ))}
      </TabWrapper>
      {dataTab?.map((item, index) => (
        <ItemRow key={`k${index}`} item={item} />
      ))}
    </>
  );
}
