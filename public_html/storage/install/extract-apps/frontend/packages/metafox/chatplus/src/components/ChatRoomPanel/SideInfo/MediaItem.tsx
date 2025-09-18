import { convertDateTime } from '@metafox/chatplus/utils';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import React from 'react';

const name = 'MediaItem';
const Root = styled('div')(({ theme }) => ({
  // flex: '33%',
  padding: theme.spacing(0.5)
}));
const ItemMedia = styled('span')(({ theme }) => ({
  cursor: 'pointer',
  display: 'block',
  width: '100px',
  aspectRatio: '1',
  backgroundSize: 'cover',
  backgroundPosition: 'center center',
  backgroundRepeat: 'no-repeat',
  backgroundOrigin: 'border-box',
  border: '1px solid rgba(0, 0, 0, 0.1)',
  position: 'relative'
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
  fontSize: '40px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  zIndex: 2,
  background: 'rgba(0, 0, 0, 0.3)',
  color: '#fff',
  pointerEvents: 'none'
});

interface Props {
  name: string;
  type: string;
  [key: string]: any;
}

function MediaItem(props: Props) {
  const { chatplus } = useGlobal();
  const { uploadedAt, keyIndex, listImages: listMedias } = props;

  return (
    <Root>
      <Tooltip
        PopperProps={{
          disablePortal: true
        }}
        title={convertDateTime(uploadedAt?.$date)}
        placement="top"
      >
        <div>
          {props?.video_thumb_url ? (
            <ItemMedia
              style={{
                backgroundImage: `url(${chatplus.sanitizeRemoteFileUrl(
                  props?.video_thumb_url
                )}&width=300)`
              }}
              onClick={e => {
                chatplus.presentImageView(
                  e,
                  {
                    id: parseInt(keyIndex),
                    video_thumb_url: chatplus.sanitizeRemoteFileUrl(
                      props?.video_thumb_url
                    ),
                    src: chatplus.sanitizeRemoteFileUrl(props?.url),
                    video_type:
                      props?.type && props.type.match('video/*')
                        ? props.type
                        : false
                  },
                  listMedias
                );
              }}
            >
              <CustomPlayButton icon="ico-play-circle-o" />
            </ItemMedia>
          ) : (
            <ItemMedia
              style={{
                backgroundImage: `url(${chatplus.sanitizeRemoteFileUrl(
                  props?.url
                )}&width=300)`
              }}
              onClick={e => {
                chatplus.presentImageView(
                  e,
                  {
                    id: parseInt(keyIndex),
                    src: chatplus.sanitizeRemoteFileUrl(props?.url)
                  },
                  listMedias
                );
              }}
            />
          )}
        </div>
      </Tooltip>
    </Root>
  );
}

export default MediaItem;
