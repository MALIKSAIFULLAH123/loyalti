/**
 * @type: dialog
 * name: chat.dialog.ImageView
 */

import { Dialog, DialogContent } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import * as React from 'react';

const name = 'dialogItemView';

const IconClose = styled('div', {
  name,
  slot: 'ico-close',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'fixed',
  top: theme.spacing(4),
  right: theme.spacing(4),
  cursor: 'pointer',
  width: theme.spacing(5),
  height: theme.spacing(5),
  fontSize: theme.mixins.pxToRem(18),
  color:
    theme.palette.mode === 'light' ? theme.palette.background.paper : '#fff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  '&:hover': {
    background: theme.palette.grey['800'],
    borderRadius: '50%'
  },
  ...(isMobile && {
    top: theme.spacing(2.5),
    right: theme.spacing(2.5)
  })
}));

const IconDownLoad = styled('div', {
  name,
  slot: 'ico-download',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'fixed',
  top: theme.spacing(4),
  right: theme.spacing(10),
  cursor: 'pointer',
  width: theme.spacing(5),
  height: theme.spacing(5),
  fontSize: theme.mixins.pxToRem(18),
  color:
    theme.palette.mode === 'light' ? theme.palette.background.paper : '#fff',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 1000,
  '&:hover': {
    background: theme.palette.grey['800'],
    borderRadius: '50%'
  },
  ...(isMobile && {
    top: theme.spacing(2.5),
    right: theme.spacing(8.125)
  })
}));
const RootDialogContent = styled(DialogContent, {
  name,
  slot: 'content',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  [theme.breakpoints.down('xs')]: {
    flexFlow: 'column'
  },
  ...(isMobile && {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: theme.palette.grey['A400']
  })
}));

const DialogImage = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative'
}));

const ImgStyled = styled('img')(({ theme }) => ({
  maxWidth: '100%',
  maxHeight: ' calc(100vh - 88px)',
  margin: 'auto'
}));

const StyleWrapperPrev = styled('div', {
  name,
  slot: 'button-prev',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'fixed',
  top: 0,
  bottom: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: theme.spacing(5),
  left: theme.spacing(4),
  zIndex: 2,
  ...(isMobile && {
    left: theme.spacing(0.5),
    zIndex: 999,
    opacity: 0.8
  })
}));
const StyleWrapperNext = styled('div', {
  name,
  slot: 'button-next',
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  position: 'fixed',
  top: 0,
  bottom: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: theme.spacing(5),
  right: theme.spacing(4),
  ...(isMobile && {
    right: theme.spacing(0.5),
    zIndex: 999,
    opacity: 0.8
  })
}));
const StyleButton = styled('div', { name, slot: 'button-image' })(
  ({ theme }) => ({
    cursor: 'pointer',
    width: theme.spacing(5),
    height: theme.spacing(5),
    borderRadius: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    background: theme.palette.grey['800'],
    '& .ico': {
      fontSize: theme.mixins.pxToRem(16),
      color: theme.palette.grey['50']
    }
  })
);

type IImage = {
  id: number | string;
  src: string;
  download_url?: string;
  is_image?: boolean;
  video_thumb_url?: string;
  video_type?: any;
  file_name?: any;
  imageId?: string;
};

type IProps = {
  image: IImage;
  images: IImage[];
  onSuccess?: () => void;
  onFailure?: () => void;
};

export default function ImageView({ image, images }: IProps) {
  const { useDialog, useIsMobile, i18n, dispatch } = useGlobal();
  const isMobile = useIsMobile();

  const { dialogProps, closeDialog } = useDialog();

  const maxKey = images && images.length - 1;
  const multiMode = images && images.length > 1;

  const initActiveImg = parseInt(image?.id) || 0;

  const [activeImageKey, setActiveImageKey] = React.useState(initActiveImg);

  const nextImage = i => {
    if (i + 1 > maxKey) {
      setActiveImageKey(0);
    } else {
      setActiveImageKey(parseInt(i) + 1);
    }
  };

  const prevImage = i => {
    if (i - 1 < 0) {
      setActiveImageKey(maxKey);
    } else {
      setActiveImageKey(parseInt(i) - 1);
    }
  };

  const handleKey = event => {
    const rtl = document.dir === 'rtl';
    let directAction = '';
    switch (event.keyCode) {
      case 27:
        closeDialog();
        break;
      case 39:
        if (rtl) {
          directAction = 'prev';
        } else {
          directAction = 'next';
        }

        break;
      case 37:
        if (rtl) {
          directAction = 'next';
        } else {
          directAction = 'prev';
        }

        break;
      default:
    }

    // right
    if (directAction === 'next' && activeImageKey !== maxKey) {
      nextImage(activeImageKey);

      return;
    }

    // left
    if (directAction === 'prev' && activeImageKey !== 0) {
      prevImage(activeImageKey);

      return;
    }
  };

  const downloadImage = (id: string) => {
    if (!id) return;

    dispatch({ type: 'chat/image/downloadItem', payload: id });
  };

  React.useEffect(() => {
    document.addEventListener('keydown', handleKey);

    return () => {
      document.removeEventListener('keydown', handleKey);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeImageKey]);

  if (!multiMode) {
    if (!image) return null;

    return (
      <Dialog
        {...dialogProps}
        data-testid="popupDetailPhoto"
        maxWidth={isMobile ? 'xl' : 'lg'}
        fullScreen={isMobile ? true : false}
        fullWidth={false}
        scroll="body"
        sx={{ backgroundColor: 'rgba(0, 0, 0, 0.9)' }}
      >
        <Tooltip
          arrow={false}
          placeholder="top"
          title={i18n.formatMessage({ id: 'press_esc_to_close' })}
        >
          <IconClose onClick={closeDialog} isMobile={isMobile}>
            <LineIcon icon="ico-close" />
          </IconClose>
        </Tooltip>
        <RootDialogContent dividers={false} isMobile={isMobile}>
          <DialogImage>
            {image?.video_type ? (
              <video
                src={image.src}
                type={image.video_type}
                controls
                autoPlay
              ></video>
            ) : (
              <ImgStyled src={image.src} alt="" />
            )}
            {image?.imageId && image?.is_image ? (
              <Tooltip
                arrow={false}
                placeholder="top"
                title={i18n.formatMessage({ id: 'download' })}
              >
                <IconDownLoad
                  onClick={() => downloadImage(image.imageId)}
                  isMobile={isMobile}
                >
                  <LineIcon icon="ico-download" />
                </IconDownLoad>
              </Tooltip>
            ) : null}
          </DialogImage>
        </RootDialogContent>
      </Dialog>
    );
  }

  return (
    <Dialog
      {...dialogProps}
      data-testid="popupDetailPhoto"
      maxWidth={isMobile ? 'xl' : 'lg'}
      fullScreen={isMobile ? true : false}
      fullWidth={false}
      scroll="body"
      sx={{ backgroundColor: 'rgba(0, 0, 0, 0.9)' }}
    >
      <Tooltip
        arrow={false}
        placeholder="top"
        title={i18n.formatMessage({ id: 'press_esc_to_close' })}
      >
        <IconClose onClick={closeDialog} isMobile={isMobile}>
          <LineIcon icon="ico-close" />
        </IconClose>
      </Tooltip>
      {activeImageKey !== 0 && (
        <StyleWrapperPrev isMobile={isMobile}>
          <StyleButton onClick={() => prevImage(activeImageKey)}>
            <LineIcon icon="ico-angle-left" />
          </StyleButton>
        </StyleWrapperPrev>
      )}
      <RootDialogContent dividers={false} isMobile={isMobile}>
        {images.map((item, index) => (
          <div key={index}>
            {item.id === activeImageKey ? (
              <DialogImage>
                {item?.video_type ? (
                  <video
                    src={item.src}
                    type={item.video_type}
                    controls
                    autoPlay
                  ></video>
                ) : (
                  <ImgStyled src={item.src} alt="" />
                )}
                {item?.imageId ? (
                  <Tooltip
                    arrow={false}
                    placeholder="top"
                    title={i18n.formatMessage({ id: 'download' })}
                  >
                    <IconDownLoad
                      onClick={() => downloadImage(item.imageId)}
                      isMobile={isMobile}
                    >
                      <LineIcon icon="ico-download" />
                    </IconDownLoad>
                  </Tooltip>
                ) : null}
              </DialogImage>
            ) : null}
          </div>
        ))}
      </RootDialogContent>
      {activeImageKey !== maxKey && (
        <StyleWrapperNext isMobile={isMobile}>
          <StyleButton onClick={() => nextImage(activeImageKey)}>
            <LineIcon icon="ico-angle-right" />
          </StyleButton>
        </StyleWrapperNext>
      )}
    </Dialog>
  );
}
