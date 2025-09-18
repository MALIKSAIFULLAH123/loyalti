/**
 * @type: block
 * name: music.block.AddMusic
 * title: Add Music Form
 * keywords: music
 * description: Add Music Form
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
import qs from 'query-string';

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

const MusicForm = ({ name, tab }: { name: string; tab: string }) => {
  const { usePageParams } = useGlobal();
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const pageParams = usePageParams();
  const dataSource = useResourceAction('music', tab, name);

  return (
    <RemoteFormBuilder
      noHeader
      dataSource={{
        ...dataSource,
        apiParams: { id: pageParams.id, ...searchParams }
      }}
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

function AddMusicBlock({ title }: BlockViewProps) {
  const [tab, setTab] = React.useState<string>('song');
  const { i18n, getAcl } = useGlobal();
  const canCreateAlbum = getAcl('music.music_album.create');
  const canCreateSong = getAcl('music.music_song.create');

  return (
    <Block>
      {canCreateSong && (
        <BlockHeader>
          <BlockTitle>
            <BackButton />
            {i18n.formatMessage({ id: title })}
          </BlockTitle>
        </BlockHeader>
      )}
      <BlockContent>
        {canCreateAlbum && canCreateSong && (
          <Tabs>
            <Tab
              data-testid="tabAddSong"
              active={tab === 'song'}
              onClick={() => setTab('song')}
            >
              {i18n.formatMessage({ id: 'song' })}
            </Tab>
            <Tab
              data-testid="tabAddAlbum"
              active={tab === 'album'}
              onClick={() => setTab('album')}
            >
              {i18n.formatMessage({ id: 'album' })}
            </Tab>
          </Tabs>
        )}
        <Panels>
          <Panel active={tab === 'song'}>
            <MusicForm name="addItem" tab="music_song" />
          </Panel>
          {canCreateAlbum && (
            <Panel active={tab === 'album'}>
              <MusicForm name="addItem" tab="music_album" />
            </Panel>
          )}
        </Panels>
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: AddMusicBlock,

  overrides: {
    noHeader: false
  }
});
