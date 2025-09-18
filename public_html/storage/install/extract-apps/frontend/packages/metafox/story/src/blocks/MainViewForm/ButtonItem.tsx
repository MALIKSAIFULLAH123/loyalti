import { useGlobal } from '@metafox/framework';
import useAddFormContext from '@metafox/story/hooks';
import { Box, styled } from '@mui/material';
import React from 'react';
import { alpha } from '@mui/system/colorManipulator';
import {
  checkFileAccept,
  isPhotoType,
  isVideoType,
  getFileType
} from '@metafox/utils';
import { MAX_VIDEO_DURATION } from '@metafox/story/constants';
import { checkImageError, readFile } from '@metafox/story/utils';
import { camelCase } from 'lodash';

const name = 'Buttonitem';

const RootStyled = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  ...(isMobile && {
    width: '100%',
    marginBottom: theme.spacing(2)
  })
}));

const ButtonItem = styled(Box, {
  name,
  shouldForwardProp: props => props !== 'isMobile' && props !== 'name'
})<{ isMobile?: boolean; name?: string }>(({ theme, isMobile, name }) => ({
  width: '220px',
  height: '330px',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  borderRadius: theme.shape.borderRadius,
  margin: theme.spacing(0, 1),
  padding: theme.spacing(2),
  overflow: 'hidden',
  wordBreak: 'break-word',
  cursor: 'pointer',
  fontSize: theme.mixins.pxToRem(16),
  color: '#fff',
  ...(isMobile && {
    width: '100%',
    height: '150px',
    padding: 0,
    margin: 0
  }),
  ...(name === 'photo_story' && {
    backgroundImage: `linear-gradient(0deg, ${alpha(
      theme.palette.primary.light,
      0.5
    )} 0%, ${alpha(theme.palette.primary.main, 0.7)} 50%,  ${
      theme.palette.primary.dark
    } 100%)`
  }),
  ...(name === 'text_story' && {
    backgroundImage: `linear-gradient(0deg, ${alpha(
      theme.palette.error.light,
      0.5
    )} 0%, ${alpha(theme.palette.error.main, 0.7)} 50%, ${
      theme.palette.error.dark
    } 100%)`
  })
}));

function ButtonItemMenu({ item }: any) {
  const { dialogBackend, i18n, useIsMobile, getSetting, useCheckFileUpload } =
    useGlobal();
  const acceptImageList = getSetting<string[]>(
    'core.file_mime_type_accepts.image'
  ) || ['image/*'];
  const acceptImage = acceptImageList.join();

  const acceptVideoList = getSetting<string[]>(
    'core.file_mime_type_accepts.video'
  ) || ['video/*'];
  const acceptVideo = acceptVideoList.join();

  const MaxVideoDuration: number =
    getSetting('story.video_duration') || MAX_VIDEO_DURATION;

  const videoServiceIsReady: boolean = getSetting(
    'story.video_service_is_ready'
  );

  const accept = React.useMemo(() => {
    if (item?.accept) {
      return item?.accept;
    }

    if (!videoServiceIsReady) {
      return acceptImage;
    }

    return `${acceptImage},${acceptVideo}`;
  }, [item?.accept, videoServiceIsReady, acceptImage, acceptVideo]);

  const [checkFiles] = useCheckFileUpload({
    accept
  });

  const isMobile = useIsMobile();

  const inputRef = React.useRef<HTMLInputElement>();

  const context = useAddFormContext();

  const handleClick = React.useCallback(() => {
    if (!item) return;

    if (item?.name === 'photo_story') {
      inputRef.current.click();

      return;
    }

    if (context?.setStatus) {
      context.setStatus(item?.name);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item]);

  const checkFileInfo = async (
    file: any,
    cb: { (duration: any, file: any): void; (arg0: number, arg1: any): void }
  ) => {
    try {
      if (isVideoType(file?.type)) {
        const video = document.createElement('video');
        video.preload = 'metadata';

        video.onloadedmetadata = async () => {
          window.URL.revokeObjectURL(video.src);
          const duration = video.duration;

          cb(duration, file);
        };

        video.src = URL.createObjectURL(file);

        return;
      }

      if (isPhotoType(getFileType(file))) {
        const imageDataUrl = await readFile(file);

        checkImageError(imageDataUrl, err => {
          if (err) {
            context.setUploading(true);

            context.setFilePhoto(file);
            context.setStatus(item?.name);
          } else {
            cb(undefined, file);
          }
        });

        return;
      }
    } catch (err) {
      // eslint-disable-next-line no-console
      console.log(err);
    }
  };

  const callbackHandleFile = React.useCallback(
    (duration: number, file: { type: any }) => {
      if (accept.includes('video/*') && isVideoType(file?.type)) {
        if (duration > MaxVideoDuration) {
          dialogBackend.alert({
            message: i18n.formatMessage(
              {
                id: 'you_can_not_upload_videos_longer_than'
              },
              {
                value: MaxVideoDuration
              }
            )
          });

          return;
        }
      }

      if (!checkFileAccept(file?.type, accept)) {
        dialogBackend.alert({
          message: i18n.formatMessage({ id: 'file_accept_type_fail' })
        });

        return;
      }

      if (file) {
        context.setFilePhoto(file);
        context.setStatus(item?.name);
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [accept, MaxVideoDuration, item?.name]
  );

  const handleFileChange = () => {
    const files = inputRef.current.files;
    const filesResult = checkFiles(files);

    if (filesResult?.length) {
      const file = filesResult[0];

      checkFileInfo(file, callbackHandleFile);
    }
  };

  if (!item) return null;

  const { label, name } = item || {};

  return (
    <RootStyled isMobile={isMobile}>
      <ButtonItem
        data-testid={camelCase(`Button Item Menu ${name}`)}
        onClick={handleClick}
        isMobile={isMobile}
        name={name}
      >
        {label}
      </ButtonItem>
      <input
        required
        ref={inputRef}
        type="file"
        accept={accept}
        multiple={false}
        onChange={handleFileChange}
        className="srOnly"
      />
    </RootStyled>
  );
}

export default ButtonItemMenu;
