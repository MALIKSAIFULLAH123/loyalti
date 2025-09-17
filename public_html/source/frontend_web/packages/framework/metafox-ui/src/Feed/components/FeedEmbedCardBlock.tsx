import { Image, ImageRatio, TImagePlayerOverlay } from '@metafox/ui';
import { getImageSrc, isNoImage } from '@metafox/utils';
import { createStyles, makeStyles } from '@mui/styles';
import { Theme } from '@mui/material';
import clsx from 'clsx';
import * as React from 'react';

type Props = {
  image?: string;
  widthImage?: string;
  heightImage?: string;
  mediaRatio?: ImageRatio;
  variant?: 'grid' | 'list';
  children?: React.ReactNode;
  link?: string;
  playerOverlay?: boolean;
  playerOverlayProps?: TImagePlayerOverlay;
  host?: string;
  resource_name?: string;
};

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      item: {
        display: 'block',
        marginBottom: theme.spacing(2)
      },
      itemOuter: {
        display: 'flex',
        borderRadius: theme.shape.borderRadius,
        border: theme.mixins.border('secondary'),
        overflow: 'hidden'
      },
      media: {
        width: (props: Props) => `${props.widthImage}`,
        height: (props: Props) => `${props.heightImage}`
      },
      grid: {
        '& $itemOuter': {
          flexDirection: 'column',
          '& $media': {
            width: '100%'
          }
        }
      },
      list: {
        '& $itemOuter': {
          flexDirection: 'row',
          [theme.breakpoints.down('xs')]: {
            flexDirection: 'column',
            '& $media': {
              width: '100%'
            }
          }
        }
      }
    }),
  { name: 'MuiFeedEmbedCardBlock' }
);

const FeedEmbedCardBlock = (props: Props) => {
  const {
    image,
    widthImage = '200px',
    heightImage = 'auto',
    mediaRatio = '11',
    variant = 'list',
    children,
    link,
    playerOverlay = false,
    playerOverlayProps = {},
    resource_name
  } = props;

  const classes = useStyles({ widthImage, heightImage });
  let showImage = true;
  const noImage = isNoImage(image);

  if (resource_name === 'blog' && noImage) {
    showImage = false;
  }

  return (
    <div className={clsx(classes.item, classes[variant])}>
      <div className={classes.itemOuter}>
        {showImage && image && (
          <div className={classes.media}>
            <Image
              link={link}
              src={getImageSrc(image)}
              aspectRatio={mediaRatio}
              playerOverlay={playerOverlay}
              playerOverlayProps={playerOverlayProps}
            />
          </div>
        )}
        {children}
      </div>
    </div>
  );
};

export default FeedEmbedCardBlock;
