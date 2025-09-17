import { Link, useGlobal } from '@metafox/framework';
import { PhotoItemProps } from '@metafox/photo/types';
import { Flag, LineIcon } from '@metafox/ui';
import { getImageSize, getImageSrc } from '@metafox/utils';
import clsx from 'clsx';
import { min } from 'lodash';
import React, { useEffect } from 'react';
import useStyles from './styles';

interface PhotoProps {
  itemHeight?: number;
  rowHeight?: number;
  width: number;
  height: number;
  margin: number;
  alt?: string;
  classes?: any;
}

const commonImageRatios = [
  16 / 9,
  9 / 16,
  4 / 3,
  5 / 4,
  3 / 4,
  1 / 2,
  2 / 1,
  1 / 1,
  1.85 / 1,
  3 / 1,
  5 / 4,
  16 / 10,
  2.35 / 1,
  2.39 / 1
];

function closest(needle, haystack) {
  return haystack.reduce((a, b) => {
    const aDiff = Math.abs(a - needle);
    const bDiff = Math.abs(b - needle);

    if (aDiff === bDiff) {
      return a > b ? a : b;
    } else {
      return bDiff < aDiff ? b : a;
    }
  });
}

function findBestFitRatio(imageActualRatio) {
  return closest(imageActualRatio, commonImageRatios);
}

function findBestFitImageSize(
  idealWidth,
  idealHeight,
  actualWidth,
  actualHeight
) {
  let calcWidth = idealWidth;

  if (actualHeight < idealHeight && actualWidth < idealWidth) {
    calcWidth = actualWidth * (idealHeight / actualHeight);
  } else {
    // imgHeight > defaultHeight && imgWidth > defaultWidth
    // imgHeight < defaultHeight && imgWidth > defaultWidth
    // imgHeight > defaultHeight &&  imgWidth < defaultWidth
    calcWidth = actualWidth / (actualHeight / idealHeight);
  }

  return {
    width: min([calcWidth, idealWidth]),
    height: idealHeight
  };
}

type Size = {
  width?: number;
  height: number;
};

const PhotoCasualItemView = ({
  item,
  identity,
  user,
  width,
  height,
  rowHeight = 250,
  handleAction,
  state,
  wrapAs: WrapAs,
  wrapProps
}: PhotoItemProps & PhotoProps) => {
  const { ItemActionMenu, assetUrl, getAcl } = useGlobal();
  const canViewReactionItem = getAcl('like.like.view');
  const classes = useStyles();
  const { id, is_featured, title, is_sponsor, statistic } = item;
  const mounted = React.useRef<boolean>(true);
  const [size, setSize] = React.useState<Size>({ width, height });
  const to = `/photo/${id}`;
  const src = getImageSrc(item.image);

  const [calculatedImageSize, setCalculatedImageSize] = React.useState({
    width: 0,
    height: 0,
    ratio: 1
  });

  const imgSrc = getImageSrc(item.image, '500', assetUrl('photo.no_image'));

  useEffect(() => {
    const fetchSize = async () => {
      const size = await getImageSize(imgSrc);

      if (mounted.current) {
        setSize(size);
      }
    };
    fetchSize();

    return () => {
      mounted.current = false;
    };
  }, [imgSrc]);

  useEffect(() => {
    const { width: widthImg, height: heightImg } = size;

    if (0 === height) return;

    const bestFitRatio = findBestFitRatio(widthImg / heightImg);
    const bestFitSize = findBestFitImageSize(
      rowHeight * bestFitRatio,
      rowHeight,
      widthImg,
      heightImg
    );
    setCalculatedImageSize({ ...bestFitSize, ratio: bestFitRatio });
  }, [height, rowHeight, size]);

  return (
    <WrapAs
      {...wrapProps}
      style={{
        flexGrow: calculatedImageSize.ratio,
        width: calculatedImageSize.width
      }}
    >
      <div
        className={clsx(
          classes.casualItem,
          state.menuOpened && classes.hoverCard
        )}
        style={{
          height: rowHeight,
          display: 'block'
        }}
      >
        <Link to={to} asModal className={classes.mediaLink}>
          <img
            src={src}
            alt={title}
            className={classes.image}
            style={{
              width: calculatedImageSize.width,
              height: calculatedImageSize.height
            }}
          />
          <div className={classes.photoInfo}>
            <div>
              <div className={classes.photoTitle}>{user.full_name}</div>
              {canViewReactionItem && statistic.total_like > 0 ? (
                <div className={classes.photoLike}>
                  <span className="ico ico-thumbup-o"></span>
                  <span className={classes.total_like}>
                    {statistic.total_like}
                  </span>
                </div>
              ) : null}
            </div>
          </div>
          <div className={classes.features}>
            {is_featured ? (
              <Flag
                type={'is_featured'}
                color={'white'}
                hasShadow
                variant={'itemView'}
              />
            ) : null}
            {is_sponsor ? (
              <Flag
                type={'is_sponsor'}
                color={'white'}
                hasShadow
                variant={'itemView'}
              />
            ) : null}
          </div>
        </Link>
        <div className={classes.photoActions}>
          <ItemActionMenu
            state={state}
            handleAction={handleAction}
            className={classes.photoActionsDropdown}
          >
            <LineIcon
              icon={'ico-dottedmore-vertical-o'}
              className={classes.iconButton}
            />
          </ItemActionMenu>
        </div>
      </div>
    </WrapAs>
  );
};

export default PhotoCasualItemView;
