import { handleConvertBase64 } from '@metafox/photo/utils/helper';
import { Image, LineIcon } from '@metafox/ui';
import { getImageSrc, isIOS } from '@metafox/utils';
import { styled } from '@mui/material';
import React from 'react';

const name = 'ComposerVideoItem';

const VideoItem = styled('video', {
  name,
  slot: 'VideoItem'
})(({ theme }) => ({
  width: '100%',
  maxWidth: '100%',
  height: '100%',
  borderRadius: theme.shape.borderRadius
}));

const CustomPlayButton = styled(LineIcon, {
  name,
  slot: 'CustomPlayButton'
})({
  position: 'absolute',
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  margin: 'auto',
  fontSize: '64px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  background: 'rgba(0, 0, 0, 0.3)',
  color: '#fff'
});

interface Props {
  item?: any;
  showImage?: boolean;
}

function VideoItemView({ item, showImage: showImageProps }: Props) {
  const thumbnail = item?.thumbnail;

  const showImage = showImageProps || thumbnail?.file || thumbnail?.image;

  const defaultThumbnailVideo = React.useMemo(() => {
    if (thumbnail?.image) return getImageSrc(thumbnail?.image, '500');

    return undefined;
  }, [thumbnail]);

  const [previewUrl, setPreviewUrl] = React.useState(defaultThumbnailVideo);

  React.useEffect(() => {
    if (!thumbnail?.file) return;

    handleConvertBase64(thumbnail?.file, result => {
      setPreviewUrl(result);
    });
  }, [thumbnail?.file]);

  const preventPlay = e => {
    e.preventDefault();
    e.stopPropagation();
  };

  return (
    <>
      {showImage ? (
        <Image
          draggable={false}
          src={`${previewUrl || item.source || item?.destination}#t=0.1`}
          aspectRatio={'169'}
          shape={'radius'}
        />
      ) : (
        <VideoItem
          src={`${item.source || item?.destination}#t=0.1`}
          draggable={false}
          controls={false}
          autoPlay={isIOS ? undefined : false}
          muted
          playsInline
          preload="metadata"
        />
      )}
      <CustomPlayButton onClick={preventPlay} icon="ico-play-circle-o" />
    </>
  );
}

export default VideoItemView;
