import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import PreviewImageComponent from '@metafox/photo/components/UploadMultiPhoto/PreviewImage';
import { LineIcon } from '@metafox/ui';
import { isIOS, isVideoType } from '@metafox/utils';
import {
  Button,
  Grid,
  styled,
  Tooltip,
  useMediaQuery,
  useTheme
} from '@mui/material';
import React, { useRef } from 'react';

export interface DropButtonProps {
  isOver?: boolean;
}

const Root = styled('div', {
  name: 'MultipleUploadField',
  slot: 'Root'
})(({ theme }) => ({
  marginTop: theme.spacing(3)
}));

const DropButton = styled(Button, {
  name: 'DropButton',
  slot: 'DropButton'
})<DropButtonProps>(({ theme, isOver }) => ({
  ...(isOver && {
    backgroundColor: theme.palette.action.hover
  })
}));

const PreviewItem = styled('div', {
  name: 'MultipleUploadField',
  slot: 'PreviewItem'
})(({ theme }) => ({
  width: '100%',
  paddingBottom: '56%',
  borderRadius: theme.shape.borderRadius,
  background: theme.palette.grey['A700'],
  position: 'relative',
  overflow: 'hidden',
  '&::before': {
    backgroundColor: theme.palette.grey['A700'],
    content: '""',
    display: 'block',
    height: '100%',
    position: 'absolute',
    width: '100%',
    borderRadius: theme.spacing(1.2)
  }
}));

const PreviewVideo = styled('video', {
  name: 'MultipleUploadField',
  slot: 'PreviewVideo'
})({
  position: 'absolute',
  left: 0,
  top: 0,
  right: 0,
  bottom: 0,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  maxWidth: '100%'
});

const RemoveBtn = styled('div', {
  name: 'MultipleUploadField',
  slot: 'removeBtn'
})(({ theme }) => ({
  width: theme.spacing(3),
  height: theme.spacing(3),
  borderRadius: theme.spacing(1.5),
  backgroundColor: 'rgba(0,0,0,0.89)',
  color: '#fff',
  position: 'absolute',
  top: theme.spacing(2),
  right: theme.spacing(2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer'
}));
const AddMoreBtnWrapper = styled('div', {
  name: 'MultipleUploadField',
  slot: 'AddMoreBtnWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderColor: theme.palette.border.primary,
  borderStyle: 'solid',
  borderWidth: 1,
  borderRadius: theme.shape.borderRadius,
  height: '100%',
  button: {
    width: '100% !important',
    height: '100% !important'
  },
  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '56%'
  }
}));

const PreviewVideoWrapper = styled('div', {
  name: 'MultipleUploadField',
  slot: 'PreviewVideoWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%'
}));

const MaskPlay = styled('div', {
  name: 'MultipleUploadField',
  slot: 'MaskPlay'
})(({ theme }) => ({
  position: 'absolute',
  width: theme.spacing(5),
  height: theme.spacing(5),
  color: '#fff',
  backgroundColor: 'rgba(0,0,0,0.4)',
  borderRadius: '50%',
  left: '50%',
  top: '50%',
  marginLeft: theme.spacing(-2.5),
  marginTop: theme.spacing(-2.5),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: theme.mixins.pxToRem(24)
}));

function UpLoad({ config, uploadFiles, setFiles, checkFilesSize }) {
  const { accept } = config;
  const { i18n, getSetting } = useGlobal();
  const inputRef = useRef<HTMLInputElement>();
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const allowUploadVideo = getSetting(
    'photo.photo_allow_uploading_video_to_photo_album'
  );

  uploadFiles = uploadFiles.filter(photo => !photo.id);

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const handleInputChange = () => {
    const files = inputRef.current.files;

    if (!files.length) return;

    checkFilesSize(Object.values(files));
  };

  const removeFile = (uid: string) =>
    setFiles(prev => prev.filter(item => item.uid !== uid));

  return (
    <ScrollContainer
      autoHide
      autoHeight
      autoHeightMax={isSmallScreen ? '90vh' : 352}
    >
      <Root>
        <input
          ref={inputRef}
          type="file"
          aria-hidden
          className="srOnly"
          multiple
          accept={accept}
          onChange={handleInputChange}
        />

        <Grid container columnSpacing={1} rowSpacing={1}>
          <Grid item sm={6} md={4}>
            <AddMoreBtnWrapper>
              <DropButton
                size="large"
                color="primary"
                startIcon={<LineIcon icon="ico-photos-plus-o" />}
                sx={{ fontWeight: 'bold' }}
                onClick={handleControlClick}
              >
                {allowUploadVideo
                  ? i18n.formatMessage({ id: 'select_files' })
                  : i18n.formatMessage({ id: 'add_photos' })}
              </DropButton>
            </AddMoreBtnWrapper>
          </Grid>
          {uploadFiles?.map((item, index) => {
            const isVideo =
              isVideoType(item?.file?.type) || item?.module_name === 'video';
            const isVideoTag = !isIOS && isVideo;

            return (
              <Grid item key={item.uid} sm={6} md={4} xs={6}>
                <PreviewItem>
                  {isVideo ? (
                    <PreviewVideoWrapper>
                      {isVideoTag ? (
                        <PreviewVideo
                          src={item?.source}
                          controls={false}
                        ></PreviewVideo>
                      ) : (
                        <PreviewImageComponent item={item} />
                      )}
                      <MaskPlay>
                        <LineIcon icon="ico-play" />
                      </MaskPlay>
                    </PreviewVideoWrapper>
                  ) : (
                    <PreviewImageComponent item={item} />
                  )}
                  <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                    <RemoveBtn onClick={() => removeFile(item.uid)}>
                      <LineIcon icon="ico-close" />
                    </RemoveBtn>
                  </Tooltip>
                </PreviewItem>
              </Grid>
            );
          })}
        </Grid>
      </Root>
    </ScrollContainer>
  );
}

export default UpLoad;
