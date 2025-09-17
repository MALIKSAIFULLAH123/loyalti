import * as React from 'react';
import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import Detail from './Detail';
import Changelog from './Changelog';
import Installation from './Installation';
import { useGlobal } from '@metafox/framework';
import { ProductContext } from '../AdminAppStoreShowDetail';

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

function TabPanel(props: TabPanelProps) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

function a11yProps(index: number) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`
  };
}

export default function BasicTabs() {
  const { i18n } = useGlobal();
  const [value, setValue] = React.useState(0);
  const item = React.useContext(ProductContext);

  const handleChange = (event: React.SyntheticEvent, newValue: number) => {
    setValue(newValue);
  };

  return (
    <Box sx={{ minHeight: { sm: '50vh', xs: 'auto' } }}>
      <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
        <Tabs value={value} onChange={handleChange}>
          <Tab
            label={i18n.formatMessage({ id: 'app_detail_details' })}
            {...a11yProps(0)}
          />
          {item?.text_installation ? (
            <Tab
              label={i18n.formatMessage({ id: 'app_detail_installation' })}
              {...a11yProps(1)}
            />
          ) : null}
          {item?.text_changelog ? (
            <Tab
              label={i18n.formatMessage({ id: 'app_detail_changelog' })}
              {...a11yProps(3)}
            />
          ) : null}
        </Tabs>
      </Box>
      <TabPanel value={value} index={0}>
        <Detail />
      </TabPanel>
      <TabPanel value={value} index={1}>
        <Installation />
      </TabPanel>
      <TabPanel value={value} index={2}>
        <Changelog />
      </TabPanel>
    </Box>
  );
}
