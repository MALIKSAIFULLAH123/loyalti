import { Link } from '@metafox/framework';
import { SongItemShape } from '@metafox/music';
import { Flag, LineIcon, TruncateText } from '@metafox/ui';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block'
      },
      itemOuter: {
        display: 'flex'
      },
      itemMedia: {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: theme.spacing(6),
        height: theme.spacing(6),
        background: theme.palette.background.default,
        marginRight: theme.spacing(2)
      },
      iconMedia: {
        color: theme.palette.text.secondary,
        fontSize: theme.spacing(3)
      },
      itemInner: {
        minWidth: 0,
        flex: 1,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      },
      title: {
        display: 'flex',
        minWidth: 0,
        flex: 1,
        fontSize: '15px',
        color: theme.palette.text.secondary
      },
      iconPlay: {
        fontSize: '15px',
        marginRight: theme.spacing(1.5)
      },
      durationWrapper: {
        display: 'flex'
      },
      duration: {
        color: theme.palette.text.secondary,
        fontSize: '15px',
        marginLeft: theme.spacing(2)
      },
      flagWrapper: {
        marginLeft: theme.spacing(2)
      }
    }),
  { name: 'MuiFeedEmbedSongMini' }
);

const FeedEmbedSongMini = ({ item }: { item: SongItemShape }) => {
  const { title, link, length = '3:14' } = item;
  const classes = useStyles();

  return (
    <div className={classes.item}>
      <div className={classes.itemOuter}>
        <div className={classes.itemMedia}>
          <LineIcon className={classes.iconMedia} icon={'ico-disc-alt'} />
        </div>
        <div className={classes.itemInner}>
          <Link className={classes.title} to={link ?? '/'}>
            <TruncateText fontSize={15} lineHeight={1.33} lines={1}>
              <LineIcon className={classes.iconPlay} icon={'ico-play'} />
              {title}
            </TruncateText>
          </Link>
          <span className={classes.durationWrapper}>
            <span className={classes.flagWrapper}>
              <Flag data-testid="featured" type={'is_featured'} />
            </span>
            <span className={classes.duration}>{length}</span>
          </span>
        </div>
      </div>
    </div>
  );
};

export default FeedEmbedSongMini;
