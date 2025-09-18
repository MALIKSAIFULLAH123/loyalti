import { useGlobal, connectItemView, useScrollEnd } from '@metafox/framework';
import { Box, Tooltip, styled } from '@mui/material';
import React, { useEffect } from 'react';
import { SongDetailViewProps } from '@metafox/music/types';
import { LineIcon } from '@metafox/ui';

const NameSong = styled(Box, {
  name: 'nameSong',
  shouldForwardProp: prop => prop !== 'isSelect'
})<{ isSelect: boolean }>(({ theme, isSelect }) => ({
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap',
  color: !isSelect
    ? theme.palette.mode === 'light'
      ? '#121212'
      : '#fff'
    : theme.palette.primary.main
}));

const ItemSongStyled = styled(Box, { name: 'itemSongStyled' })(({ theme }) => ({
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  borderBottom: `1px solid ${theme.palette.border.secondary}`,
  padding: theme.spacing(1.5, 0)
}));

const ListStyled = styled(Box, { name: 'listStyled' })(({ theme }) => ({
  '& div:last-child': {
    borderBottom: 0
  }
}));

const PendingSong = styled(Box, { name: 'PendingSong' })(({ theme }) => ({
  marginLeft: 'auto',
  color: theme.palette.text.hint,
  display: 'flex',
  alignItems: 'center'
}));

function SongItemList({
  song,
  identity,
  handleAction,
  selectedSong,
  setSelectedSong,
  state,
  item,
  isAlbum
}: SongDetailViewProps) {
  const { ItemActionMenu, i18n } = useGlobal();

  if (!item) return;

  const handleSelectedSong = () => {
    if (selectedSong.id !== song.id) {
      setSelectedSong(song);
    }
  };

  return (
    <ItemSongStyled>
      <NameSong
        isSelect={selectedSong?.id === song?.id}
        onClick={handleSelectedSong}
      >
        {song.name}
      </NameSong>
      {song?.is_pending && (
        <PendingSong>
          <Tooltip
            title={i18n.formatMessage({ id: 'pending' })}
            placement="top"
          >
            <LineIcon icon="ico-clock-o" />
          </Tooltip>
        </PendingSong>
      )}
      <ItemActionMenu
        menuName={isAlbum ? 'itemActionMenu' : 'itemActionMenuOnPlaylist'}
        sx={{ ml: 2 }}
        identity={identity}
        icon={'ico-dottedmore-vertical-o'}
        state={state}
        handleAction={handleAction}
      />
    </ItemSongStyled>
  );
}

const ItemView = connectItemView(SongItemList, () => {});

const PlayList = ({
  songs,
  selectedSong,
  setSelectedSong,
  identity,
  handleAction,
  state,
  isPlaying,
  isAlbum,
  item
}: SongDetailViewProps) => {
  const { dispatch } = useGlobal();
  const [loading, setLoading] = React.useState(false);

  useEffect(() => {
    setSelectedSong(songs[0]);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (!loading && item?.statistic?.total_song > songs.length) {
      dispatch({
        type: 'music/loadMoreListSong',
        meta: {
          onSuccess: () => {
            setLoading(true);
          }
        },
        payload: { identity }
      });
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loading]);

  useScrollEnd(() => {
    setLoading(false);
  });

  return (
    <ListStyled>
      {songs &&
        songs.map((song, index) => (
          <ItemView
            isAlbum={isAlbum}
            song={song}
            selectedSong={selectedSong}
            setSelectedSong={setSelectedSong}
            key={index}
            index={index}
            identity={`music.entities.music_song.${song.id}`}
          />
        ))}
    </ListStyled>
  );
};

export default PlayList;
