import { useGetItem, useGlobal } from '@metafox/framework';
import { Image, LineIcon, TruncateText } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box, CircularProgress, styled, Tooltip } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';

const name = 'AttachmentItem';

const IconDownStyled = styled(Box, {
  shouldForwardProp: props =>
    props !== 'downloading' && props !== 'sizeDownloadButton'
})<{ downloading?: boolean; sizeDownloadButton?: string }>(
  ({ theme, downloading, sizeDownloadButton }) => ({
    cursor: 'pointer',
    borderRadius: theme.spacing(0.5),
    borderColor: theme.palette.primary.main,
    borderStyle: 'solid',
    borderWidth: 1,
    width: 40,
    height: 40,
    display: 'flex',
    alignContent: 'space-around',
    flexWrap: 'wrap',
    justifyContent: 'center',
    position: 'absolute',
    right: theme.spacing(2),
    '& span': {
      fontSize: theme.mixins.pxToRem(18),
      color: theme.palette.primary.main,
      marginTop: 'auto',
      marginBottom: 'auto',
      ...(sizeDownloadButton === 'mini' && {
        fontSize: theme.mixins.pxToRem(14)
      })
    },
    ...(downloading && {
      background: theme.palette.action.disabledBackground,
      borderColor: theme.palette.action.disabledBackground,
      opacity: theme.palette.action.disabledOpacity,
      cursor: 'auto'
    }),
    ...(sizeDownloadButton === 'large' && {
      right: theme.spacing(4)
    }),
    ...(sizeDownloadButton === 'mini' && {
      width: 32,
      height: 32
    })
  })
);

const AttachmentWrapper = styled(Box, {
  shouldForwardProp: props => props !== 'size'
})<{ size?: string }>(({ theme, size }) => ({
  borderRadius: theme.shape.borderRadius,
  border: theme.mixins.border('secondary'),
  overflow: 'hidden',
  display: 'flex',
  width: '100%',
  position: 'relative',
  alignItems: 'center',
  ...(size === 'large' && {
    height: 112
  }),
  ...(size === 'mini' && {
    '& statistic': {
      width: 'calc(100% - 100px)'
    }
  })
}));

const AttachmentPhoto = styled(Box, {
  shouldForwardProp: props => props !== 'size'
})<{ size?: string }>(({ theme, size }) => ({
  width: 112,
  marginRight: theme.spacing(2),
  borderRadius: 0,
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderTopLeftRadius: theme.shape.borderRadius,
  textAlign: 'center',
  overflow: 'hidden',
  borderRight: '1px solid',
  borderRightColor: theme.palette.border.secondary,
  ...(size === 'mini' && {
    width: 64
  })
}));

const AttachmentTypeIcon = styled(LineIcon, {
  name,
  slot: 'attachmentTypeIcon'
})(({ theme }) => ({
  margin: theme.spacing(0, 2, 0, 1),
  fontSize: theme.mixins.pxToRem(48),
  color: theme.palette.text.secondary
}));

const FileSize = styled(Box, {
  name,
  slot: 'fileSize'
})(({ theme }) => ({
  color: theme.palette.text.secondary,
  fontSize: theme.mixins.pxToRem(13)
}));

const Statistic = styled(Box, {
  name,
  slot: 'statistic'
})(({ theme }) => ({
  width: 'calc(100% - 200px)',
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'space-around',
  height: 48
}));

export type AttachmentItemProps = {
  fileName: string;
  downloadUrl?: string;
  identity: string;
  isImage: boolean;
  fileSizeText?: string;
  size?: 'small' | 'large' | 'mini';
  image?: Record<number, any>;
  index?: number
};

export default function AttachmentItem(props: AttachmentItemProps) {
  const {
    fileName,
    isImage,
    fileSizeText,
    size = 'small',
    image,
    identity,
    index
  } = props;
  const { dispatch, getSetting, i18n } = useGlobal();
  const downloadIconRef = React.useRef<HTMLAnchorElement>();
  const setting = getSetting('attachment');
  const [downloading, setDownloading] = React.useState(false);
  const item = useGetItem(identity);
  const [widthDownIcon, setWidthDownIcon] = React.useState('40px');

  React.useEffect(() => {
    const width =
      downloadIconRef.current &&
      `${downloadIconRef.current.getBoundingClientRect().width}px`;

    setWidthDownIcon(width);
  }, []);

  const icon = isImage ? 'ico-file-photo-o' : 'ico-file-zip-o';
  const photo = getImageSrc(image, '500');
  const photoFull = getImageSrc(image, 'origin');

  const presentPhoto = src => {
    dispatch({
      type: 'photo/presentSimplePhoto',
      payload: { src, alt: 'photo', identity }
    });
  };

  const handleDownloading = () => setDownloading(false);

  const downloadItem = () => {
    if (downloading) return;

    setDownloading(true);
    dispatch({
      type: 'core/downloadItem',
      payload: { identity },
      meta: { onSuccess: handleDownloading, onFailure: handleDownloading }
    });
  };

  return (
    <AttachmentWrapper
      size={size}
    >
      <AttachmentPhoto size={size} data-testid={camelCase(`item_photo ${index + 1}`)}>
        {isImage ? (
          <div onClick={() => presentPhoto(photoFull)} role="button">
            <Image aspectRatio="11" src={photo} alt={'photo'} />
          </div>
        ) : (
          <AttachmentTypeIcon icon={icon} />
        )}
      </AttachmentPhoto>

      <Statistic>
        <TruncateText
          lines={1}
          variant="body1"
          sx={{ paddingRight: widthDownIcon }}
        >
          {fileName}
        </TruncateText>
        <FileSize>{fileSizeText}</FileSize>
      </Statistic>
      {setting && item?.extra?.can_download ? (
        <Tooltip
          title={i18n.formatMessage({
            id: downloading ? 'downloading' : 'download'
          })}
        >
          <IconDownStyled
            downloading={downloading}
            ref={downloadIconRef}
            onClick={downloadItem}
            sizeDownloadButton={size}
            data-testid={camelCase(`button_dowload ${index + 1}`)}
          >
            {downloading ? (
              <CircularProgress size={18} color="info" />
            ) : (
              <LineIcon icon={'ico-download'} />
            )}
          </IconDownStyled>
        </Tooltip>
      ) : null}
    </AttachmentWrapper>
  );
}
