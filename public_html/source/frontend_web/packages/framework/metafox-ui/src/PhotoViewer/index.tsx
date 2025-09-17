/**
 * @type: dialog
 * name: ui.dialog.photoViewer
 */
import { useGlobal } from '@metafox/framework';
import { Box, Dialog, IconButton } from '@mui/material';
import React, { MouseEvent, useRef } from 'react';
import LineIcon from '../LineIcon';
import useStyles from './styles';

interface Props {
  src: string;
}

const PhotoViewer = ({ src, preventZoom = false }: Props) => {
  const { useDialog, useIsMobile } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const isMobile = useIsMobile();

  const classes = useStyles();
  const imgRef = useRef<HTMLDivElement>();

  const getCursorPos = (e: MouseEvent<HTMLDivElement>) => {
    let x = 0;
    let y = 0;

    const a = imgRef.current.getBoundingClientRect();

    x = e.pageX - a.left;
    y = e.pageY - a.top;
    x = x - window.pageXOffset;
    y = y - window.pageYOffset;

    return { x, y, width: a.width, height: a.height };
  };

  const handleMouseMove = (e: MouseEvent<HTMLDivElement>) => {
    const pos = getCursorPos(e);
    const percentX = (pos.x / pos.width) * 100;
    const percentY = (pos.y / pos.height) * 100;

    imgRef.current.style.backgroundSize = '300%';
    imgRef.current.style.backgroundPositionX = `${percentX}%`;
    imgRef.current.style.backgroundPositionY = `${percentY}%`;
  };

  const handleMouseOut = () => {
    imgRef.current.style.backgroundSize = 'contain';
    imgRef.current.style.backgroundPosition = 'center';
  };

  return (
    <Dialog {...dialogProps} maxWidth="lg">
      {isMobile ? (
        <Box sx={{ display: 'block', textAlign: 'end' }}>
          <IconButton size="large" onClick={() => closeDialog()}>
            <LineIcon icon="ico-close-circle-o" />
          </IconButton>
        </Box>
      ) : null}
      <div className={classes.previewWrapper}>
        <div
          ref={imgRef}
          className={classes.previewZoom}
          onMouseMove={preventZoom ? undefined : handleMouseMove}
          onMouseOut={preventZoom ? undefined : handleMouseOut}
          style={{
            backgroundImage: `url(${src})`
          }}
        />
        <img src={src} alt="" className={classes.imgPreview} />
      </div>
    </Dialog>
  );
};

export default PhotoViewer;
