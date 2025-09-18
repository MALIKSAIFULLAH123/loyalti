import { ScrollContainer } from '@metafox/layout';
import * as React from 'react';

interface TabPanelProps {
  children?: React.ReactNode;
  index: number;
  value: number;
}

export function a11yProps(key: number) {
  return {
    id: `simple-tab-${key}`,
    'aria-controls': `simple-tabpanel-${key}`
  };
}

export default function TabPanel(props: TabPanelProps) {
  const scrollRefList = React.useRef<HTMLDivElement>();

  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      style={{
        height: '100%'
      }}
    >
      {value === index ? (
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={'100%'}
          ref={scrollRefList}
        >
          {React.cloneElement(children, { ...other })}
        </ScrollContainer>
      ) : null}
    </div>
  );
}
