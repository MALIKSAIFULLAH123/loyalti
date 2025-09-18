/**
 * @type: block
 * name: livestream.block.ShareLivestreamBlock
 * title: Share Livestream Form
 * keywords: livestreaming
 * description: Share Livestream Form
 * experiment: true
 */
import { RemoteFormBuilder } from '@metafox/form';
import {
  BlockViewProps,
  createBlock,
  useGlobal,
  useResourceAction,
  useLocation
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import {
  APP_LIVESTREAM,
  RESOURCE_LIVE_VIDEO
} from '@metafox/livestreaming/constants';
import qs from 'query-string';
import { filterShowWhen } from '@metafox/utils';

const Tabs = styled('div', {
  name: 'Tab',
  slot: 'container'
})<{}>(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row'
}));

const Tab = styled('div', {
  name: 'Tab',
  slot: 'item',
  shouldForwardProp: prop => prop !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  cursor: 'pointer',
  fontWeight: theme.typography.fontWeightBold,
  fontSize: theme.mixins.pxToRem(15),
  padding: theme.spacing(2, 0),
  marginRight: theme.spacing(3.75),
  color: theme.palette.text.secondary,
  borderBottom: 'solid 2px',
  borderBottomColor: 'transparent',
  ...(active && {
    color: theme.palette.primary.main,
    borderBottomColor: theme.palette.primary.main
  })
}));

const Panels = styled(Box, {
  name: 'Tab',
  slot: 'panels'
})<{}>(({ theme }) => ({}));

const Panel = styled(Box, {
  name: 'Tab',
  slot: 'panel'
})<{ active?: boolean }>(({ theme, active }) => ({
  display: active ? 'block' : 'none'
}));

const LivestreamForm = ({ name }: { name: string }) => {
  const dataSource = useResourceAction(
    APP_LIVESTREAM,
    RESOURCE_LIVE_VIDEO,
    name
  );
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  return (
    <RemoteFormBuilder
      noHeader
      dataSource={dataSource}
      pageParams={searchParams}
      allowRiskParams
      preventReload
    />
  );
};

const BackButton = ({ icon = 'ico-arrow-left', ...restProps }) => {
  const { navigate } = useGlobal();

  const handleClick = () => {
    navigate(-1);
  };

  return (
    <IconButton
      size="small"
      role="button"
      id="back"
      data-testid="buttonBack"
      sx={{ transform: 'translate(-5px,0)' }}
      onClick={handleClick}
      {...restProps}
    >
      <LineIcon icon={icon} />
    </IconButton>
  );
};

const tabsMenu = [
  {
    tab: 'software',
    label: 'streaming_software',
    value: 'addItem'
  },
  {
    tab: 'webcam',
    label: 'webcam',
    value: 'addWebcamItem',
    showWhen: [
      'and',
      ['truthy', 'setting.allow_webcam_streaming'],
      ['falsy', 'isMobileDevice'],
      ['truthy', 'isMediaRecorderSupported']
    ]
  }
];

const isMobileDevice =
  /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
    navigator.userAgent
  );

const isMediaRecorderSupported = typeof MediaRecorder !== 'undefined';

function ShareLivestreamBlock({ title }: BlockViewProps) {
  const { i18n, getSetting } = useGlobal();
  const setting = getSetting('livestreaming');
  const tabs = filterShowWhen(tabsMenu, {
    setting,
    isMobileDevice,
    isMediaRecorderSupported
  });
  const [activeTab, setActiveTab] = React.useState<string>(tabs[0]?.tab);

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>
          <BackButton />
          {i18n.formatMessage({ id: title })}
        </BlockTitle>
      </BlockHeader>
      <BlockContent>
        <Tabs>
          {tabs.map(({ tab, label }) => (
            <Tab
              key={tab}
              active={activeTab === tab}
              onClick={() => setActiveTab(tab)}
            >
              {i18n.formatMessage({ id: label || 'label' })}
            </Tab>
          ))}
        </Tabs>
        <Panels>
          {tabs.map(({ tab, value }) =>
            (activeTab === tab ? (
              <Panel key={tab} active={activeTab === tab}>
                <LivestreamForm name={value} />
              </Panel>
            ) : null)
          )}
        </Panels>
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: ShareLivestreamBlock,

  overrides: {
    noHeader: false
  }
});
