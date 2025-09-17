import { useGlobal } from '@metafox/framework';
import { Box, Tab, Tabs, styled } from '@mui/material';
import React from 'react';
import TabContent from './TabContent';

const TabWrapper = styled(Tab, { name: 'TabWrapper' })(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontWeight: 'bold',
  fontSize: theme.mixins.pxToRem(15),
  padding: '0 !important',
  width: 'fit-content !important',
  minWidth: 'fit-content !important',
  marginLeft: theme.spacing(2.5),
  '&:first-of-type': {
    marginLeft: theme.spacing(0)
  }
}));

const WrapperTab = styled('div', { name: 'SubTabWrapper' })<{
  pageDetail?: boolean;
}>(({ theme }) => ({
  paddingBottom: theme.spacing(1.875),
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  [theme.breakpoints.down('md')]: {
    flexDirection: 'column-reverse',
    alignItems: 'flex-start'
  }
}));

type TabProps = {
  label: string;
  name: string;
  value: string;
};

type TabContainerProps = {
  tabs?: TabProps[];
  defaultTab?: string;
};

export default function TabContainer({ tabs, defaultTab }: TabContainerProps) {
  const { i18n } = useGlobal();
  const tabActiveInitial = tabs?.length ? tabs[0]?.name : '';
  const [tabActive, setTabActive] = React.useState<string>(
    defaultTab || tabActiveInitial
  );

  const handleChange = (_: any, tab: any) => {
    setTabActive(tab);
  };

  return (
    <Box>
      <WrapperTab>
        <Tabs
          value={tabActive}
          onChange={handleChange}
          textColor="primary"
          indicatorColor="primary"
        >
          {tabs.map((tab, index) => (
            <TabWrapper
              aria-label={tab.name}
              value={tab.name}
              label={i18n.formatMessage({ id: tab.label })}
              key={index.toString()}
            />
          ))}
        </Tabs>
      </WrapperTab>
      <Box>
        {tabs.map((tab, index) => (
          <TabContent
            key={index.toString()}
            isActive={tab.name === tabActive}
            actionName={tab.value}
          />
        ))}
      </Box>
    </Box>
  );
}
