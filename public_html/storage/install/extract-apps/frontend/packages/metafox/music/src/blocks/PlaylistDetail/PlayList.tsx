import {
  useGlobal,
  connectItemView,
  GlobalState,
  getItemsSelector,
  useScrollEnd
} from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Theme,
  Table,
  TableHead,
  TableRow,
  TableBody,
  TableCell,
  styled as Styled,
  Tooltip,
  Box
} from '@mui/material';
import { createStyles, makeStyles, styled } from '@mui/styles';
import tableCellClasses from '@mui/material/TableCell/tableCellClasses';
import React, { useEffect, useState } from 'react';
import { SongDetailViewProps } from '@metafox/music/types';
import { useSelector } from 'react-redux';
import { camelCase } from 'lodash';

const secondToMinutes = (second: Number) =>
  `${Math.floor(second / 60)}:${second % 60 < 10 ? 0 : ''}${second % 60}`;

const Root = Styled(Box, { name: 'playlist' })(({ theme }) => ({
  marginBottom: theme.spacing(-2),
  '& .ico': {
    cursor: 'pointer'
  },
  [theme.breakpoints.down('xs')]: {
    '& $songOrder, & $songTime': {
      display: 'none'
    },
    '& $songTitle': {
      marginLeft: 0
    },
    '& $songActionMenu': {
      marginRight: theme.spacing(-2)
    }
  }
}));

const StyledTableCell = styled(TableCell)(({ theme }) => ({
  borderColor: theme.palette.border.secondary,
  [`&.${tableCellClasses.head}`]: {
    backgroundColor: theme.palette.background.paper,
    color: theme.palette.text.hint,
    fontWeight: theme.typography.fontWeightSemiBold,
    fontSize: theme.mixins.pxToRem(15),
    borderColor: theme.palette.border.secondary
  },
  [`&.${tableCellClasses.body}`]: {
    fontSize: theme.mixins.pxToRem(15),
    color: theme.palette.text.hint,
    marginBottom: theme.spacing(1),
    borderColor: theme.palette.border.secondary,
    padding: theme.spacing(0, 2)
  }
}));

const TableBodyStyled = Styled(TableBody, { name: 'tableRowStyled' })(
  ({ theme }) => ({
    color: 'red',
    '& tr:last-child': {
      '& td': {
        borderBottom: 0
      }
    }
  })
);

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      songItem: {
        height: theme.spacing(7),
        '& $songTitle': {
          color: theme.palette.mode === 'light' ? '#121212' : '#fff'
        },
        padding: theme.spacing(1),
        '& .ico-play': {
          display: 'none'
        },
        '& .ico-heart-o': {
          display: 'none',
          color: theme.palette.text.hint
        },

        '& .ico-heart': {
          color: theme.palette.primary.main
        },
        '& .ico-dottedmore-vertical-o': {
          display: 'none'
        },

        '&:hover': {
          '& $songPlayPause': {
            '& .ico-play': {
              display: 'block'
            },
            '& .ico-pause': {
              display: 'block'
            }
          },
          '& .ico-heart-o': {
            display: 'unset'
          },
          backgroundColor: theme.palette.background.default,
          '& $songOrderText': {
            display: 'none'
          },
          '& .ico-dottedmore-vertical-o': {
            display: 'unset'
          }
        }
      },
      songInner: {
        display: 'flex',
        alignItems: 'center',
        color: theme.palette.text.secondary,
        fontSize: 18
      },
      songOrderText: {},
      songPlaying: {
        '& $songTime': {},
        '& $songTitle': {
          color: theme.palette.primary.main
        },
        '& $songOrderText': {
          display: 'none'
        },
        '& $songPlayPause': {
          '& .ico-play': {
            display: 'block'
          },
          '& .ico-pause': {
            display: 'block'
          }
        },

        display: 'block'
      },
      songOrder: {
        paddingLeft: theme.spacing(1),
        minWidth: 32,
        textAlign: 'center'
      },
      songPlayPause: {
        color: theme.palette.text.secondary
      },
      songTitle: {
        cursor: 'pointer',
        wordBreak: 'break-word'
      },
      songActionMenu: {
        display: 'auto',
        marginLeft: 'auto',
        marginRight: theme.spacing(2),
        '& .ico': {
          color: theme.palette.text.secondary,
          fontSize: 13
        }
      },
      songTime: {
        width: '150px'
      },
      songTotal: {
        width: '100px'
      }
    }),
  { name: 'PlayList' }
);

function SongItemList({
  identity,
  handleAction,
  state,
  selectedSong,
  index,
  classes,
  isPlaying,
  setIsPlaying,
  setSelectedSong,
  item,
  isAlbum
}: SongDetailViewProps) {
  const { ItemActionMenu, i18n } = useGlobal();

  if (!item) return;

  const handleSelectedSong = () => {
    if (selectedSong.id !== item.id) {
      setSelectedSong(item);
      setIsPlaying(true);
    } else setIsPlaying(prev => !prev);
  };

  return (
    <TableRow
      data-testid={camelCase('music playlist table row')}
      className={`${classes.songItem} ${
        item?.id === selectedSong?.id ? classes.songPlaying : ''
      }`}
    >
      <StyledTableCell>
        <div className={classes.songOrderText}>{index + 1}</div>
        <div className={classes.songPlayPause} onClick={handleSelectedSong}>
          {isPlaying && item?.id === selectedSong?.id ? (
            <LineIcon icon="ico-pause" />
          ) : (
            <LineIcon icon="ico-play" />
          )}
        </div>
      </StyledTableCell>
      <StyledTableCell
        className={classes.songTitle}
        onClick={handleSelectedSong}
      >
        {item.name}
      </StyledTableCell>
      <StyledTableCell align="right">
        {item?.is_pending && (
          <Tooltip
            title={i18n.formatMessage({ id: 'pending' })}
            placement="top"
          >
            <LineIcon icon="ico-clock-o" />
          </Tooltip>
        )}
      </StyledTableCell>
      <StyledTableCell align="right" className={classes.songTotal}>
        {item.statistic?.music_song_total_play}
      </StyledTableCell>
      <StyledTableCell align="right" className={classes.songTime}>
        {item?.is_favorite !== undefined && (
          <LineIcon
            icon={item?.is_favorite ? 'ico-heart' : 'ico-heart-o'}
            sx={{ fontSize: '14px', mr: 2 }}
          />
        )}
        {secondToMinutes(item?.duration)}

        <ItemActionMenu
          menuName={isAlbum ? 'itemActionMenu' : 'itemActionMenuOnPlaylist'}
          sx={{ ml: 2 }}
          identity={`music.entities.music_song.${item.id}`}
          icon={'ico-dottedmore-vertical-o'}
          state={state}
          handleAction={handleAction}
          className={classes.songActionMenu}
        />
      </StyledTableCell>
    </TableRow>
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
  setIsPlaying,
  isAlbum,
  item
}: SongDetailViewProps) => {
  const classes = useStyles();
  const { i18n, dispatch } = useGlobal();
  const [loading, setLoading] = useState(false);

  const listSongs = useSelector((state: GlobalState) =>
    getItemsSelector(state, songs)
  );

  useEffect(() => {
    setSelectedSong(listSongs[0]);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [identity]);

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
    <Root>
      <div className="playlist">
        <Table
          data-testid={camelCase('music playlist table')}
          sx={{ fontSize: '15px' }}
        >
          <TableHead data-testid={camelCase('music playlist table head')}>
            <TableRow>
              <StyledTableCell style={{ width: '50px' }}>#</StyledTableCell>
              <StyledTableCell>
                {i18n.formatMessage({ id: 'title' })}
              </StyledTableCell>
              <StyledTableCell align="right" />
              <StyledTableCell align="right">
                {i18n.formatMessage({ id: 'music_plays' })}
              </StyledTableCell>
              <StyledTableCell align="center">
                {i18n.formatMessage({ id: 'duration' })}
              </StyledTableCell>
            </TableRow>
          </TableHead>
          <TableBodyStyled data-testid={camelCase('music playlist table body')}>
            {listSongs &&
              listSongs.map((song, index) => (
                <ItemView
                  isAlbum={isAlbum}
                  song={song}
                  selectedSong={selectedSong}
                  setSelectedSong={setSelectedSong}
                  key={index}
                  index={index}
                  classes={classes}
                  isPlaying={isPlaying}
                  setIsPlaying={setIsPlaying}
                  identity={`music.entities.music_song.${song.id}`}
                />
              ))}
          </TableBodyStyled>
        </Table>
      </div>
    </Root>
  );
};

export default PlayList;
