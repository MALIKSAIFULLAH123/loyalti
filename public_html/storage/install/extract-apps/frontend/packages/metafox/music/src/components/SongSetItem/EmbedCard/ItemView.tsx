import { SongItemShape } from '@metafox/music';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import clsx from 'clsx';
import * as React from 'react';
import SongCardMini from './SongCardMini';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      listing: {
        display: 'block'
      },
      oneType: {},
      multipleType: {
        borderRadius: theme.shape.borderRadius,
        borderWidth: '1px',
        borderStyle: 'solid',
        borderColor: theme.palette.border?.secondary,
        padding: theme.spacing(2),
        '& $item:not(:last-child)': {
          paddingBottom: theme.spacing(2),
          marginBottom: theme.spacing(2),
          borderBottomWidth: '1px',
          borderBottomStyle: 'solid',
          borderColor: theme.palette.border?.secondary
        }
      },
      item: {}
    }),
  { name: 'MuiFeedEmbedSongList' }
);

const FeedEmbedSongList = ({ items }: { items: SongItemShape[] }) => {
  const classes = useStyles();

  return (
    <div className={clsx(classes.listing, classes.multipleType)}>
      {items.length &&
        items.map((item, index) => (
          <div className={classes.item} key={index.toString()}>
            <SongCardMini item={item} />
          </div>
        ))}
    </div>
  );
};

export default FeedEmbedSongList;
