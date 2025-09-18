import { FETCH_PREVIEW_PHOTO, useGlobal } from '@metafox/framework';
import {
  DEFAULT_COLOR,
  DEFAULT_FONTSIZE,
  DEFAULT_FONTSTYLE,
  IMAGE_PREVIEW_BACKGROUND,
  KEY_TEXT_EXCLUDES,
  StoryPreviewChanged,
  WidthInteractionLandscape
} from '@metafox/story/constants';
import useAddFormContext, { useGetSizeContainer } from '@metafox/story/hooks';
import { Box, Button, Typography, styled, useMediaQuery } from '@mui/material';
import { escape, get, isEmpty, isEqual, isNil, omit, uniqueId } from 'lodash';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import MuiButton from '@mui/lab/LoadingButton';
import { isVideoType } from '@metafox/utils';
import loadable from '@loadable/component';
import { DraggableData } from 'react-draggable';
import MenuTextItem from './MenuTextItem';
import {
  BlockLoading,
  BlockUploadingFile,
  CropImage,
  FontComponent,
  FormStoryMobile,
  SizeImgProp,
  initStateCrop,
  reducerCrop
} from '@metafox/story/components';
import { mappingRotate, readFile } from '@metafox/story/utils';
import { captureVideo } from '@metafox/story/utils/captureVideo';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';
import StoryEditor from '../Composer/StoryEditor';
import TextItemDraggable from './TextItemDraggable';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const name = 'StoryReview';

const RootStyled = styled('div', {
  name,
  slot: 'RootStyled'
})(({ theme }) => ({
  height: '100%',
  width: '100%',
  position: 'relative',
  display: 'flex',
  flexDirection: 'column',
  background: '#000',
  paddingTop: theme.spacing(1),
  alignItems: 'center'
}));

const ItemWrapper = styled('div', {
  name,
  slot: 'ItemWrapper'
})(({ theme }) => ({
  position: 'relative',
  margin: 'auto',
  height: '100%',
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  display: 'flex',
  justifyContent: 'center',
  flexDirection: 'column',
  alignItems: 'center'
}));

const FooterStyled = styled(Box, {
  name,
  slot: 'FooterStyled',
  shouldForwardProp: props => props !== 'width' && props !== 'isMinHeight'
})<{ width?: any; isMinHeight?: any }>(({ theme, width, isMinHeight }) => ({
  minHeight: '80px',
  zIndex: 2,
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  background: '#000',
  padding: theme.spacing(0, 1),
  ...(width && {
    width
  }),
  ...(isMinHeight && {
    maxWidth: WidthInteractionLandscape
  })
}));

const StoryImageContainer = styled('div', {
  name,
  slot: 'StoryImageContainer',
  shouldForwardProp: props =>
    props !== 'width' && props !== 'height' && props !== 'init'
})<{ height?: number; width?: number; init?: boolean }>(
  ({ theme, height, width, init }) => ({
    height: height ? height : '100%',
    width: width ? width : '390px',
    position: 'relative',
    overflow: 'hidden',
    borderRadius: theme.shape.borderRadius,
    border: theme.mixins.border('secondary'),
    borderColor: '#fff',
    ...(init && {
      opacity: 0.1,
      borderWidth: 0
    })
  })
);

const ButtonBackIcon = styled(Button, { name, slot: 'backpage' })(
  ({ theme }) => ({
    position: 'absolute',
    top: 16,
    left: 8,
    color: '#fff',
    fontSize: theme.spacing(3),
    minWidth: theme.spacing(5),
    padding: 0,
    margin: 0,
    zIndex: 2,
    '& span.ico': {
      fontWeight: theme.typography.fontWeightBold
    }
  })
);

const ButtonMutedIcon = styled(Button, { name, slot: 'mutedIcon' })(
  ({ theme }) => ({
    position: 'absolute',
    top: 16,
    right: 60,
    color: '#fff',
    fontSize: theme.spacing(3),
    minWidth: theme.spacing(5),
    padding: 0,
    margin: 0,
    zIndex: 2,
    '& span.ico': {
      fontWeight: theme.typography.fontWeightBold
    }
  })
);

const StoryImage = styled('div', {
  name,
  slot: 'StoryImage'
})(({ theme }) => ({
  height: '100%'
}));

const BackgroundBlur = styled('div', {
  name,
  slot: 'BackgroundBlur',
  shouldForwardProp: props => props !== 'type'
})<{ type?: any }>(({ theme, type }) => ({
  filter: 'blur(30px)',
  WebkitFilter: 'blur(30px)',
  overflow: 'hidden',
  padding: theme.spacing(1.5),
  height: '100%',
  width: '100%',
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  transform: `scale(${type === 'video' ? 4 : 2})`
}));

const EditorBlockStyled = styled('div', { name, slot: 'EditorBlockStyled' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    top: '100px',
    right: 0,
    margin: theme.spacing(0, 3)
  })
);
const InputContent = styled('div', {
  name,
  slot: 'InputContent',
  shouldForwardProp: prop => prop !== 'fontFamily'
})<{ fontFamily?: string }>(({ theme, fontFamily }) => ({
  height: '100%',
  overflow: 'hidden',
  padding: theme.spacing(3),
  position: 'absolute',
  left: 0,
  top: 0,
  right: 0,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  textAlign: 'center',
  zIndex: 1,
  fontWeight: theme.typography.fontWeightRegular,
  fontSize: theme.mixins.pxToRem(28),
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  color: '#fff',
  '& a': {
    color: '#fff'
  },
  ...(fontFamily && {
    fontFamily
  })
}));

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  cursor: 'move'
}));

export const TextContent = styled('div', {
  name,
  slot: 'TextContent',
  shouldForwardProp: prop => prop !== 'fontFamily' && prop !== 'position'
})<{ fontFamily?: string; position?: boolean }>(
  ({ theme, fontFamily, position }) => ({
    maxHeight: '100%',
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0,
    textAlign: 'center',
    zIndex: 1,
    fontWeight: theme.typography.fontWeightRegular,
    fontSize: theme.mixins.pxToRem(28),
    wordBreak: 'break-word',
    wordWrap: 'break-word',
    color: '#fff',
    '& a': {
      color: '#fff'
    },
    ...(fontFamily && {
      fontFamily
    }),
    width: 'fit-content',
    height: 'fit-content',
    boxSizing: 'border-box',
    border: theme.mixins.border('primary'),
    borderColor: 'transparent',
    cursor: 'move',
    lineHeight: 'normal',
    '&:hover': {
      borderColor: '#fff'
    }
  })
);

const PrivacyStyled = styled(Box, { name, slot: 'PrivacyStyled' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    flexDirection: 'column',
    color: '#fff',
    fontSize: theme.mixins.pxToRem(20)
  })
);
const ButtonSubmit = styled(Box, { name, slot: 'ButtonSubmit' })(
  ({ theme }) => ({})
);

const AddTextStyled = styled(Box)(({ theme }) => ({
  width: '40px',
  height: '40px',
  padding: theme.spacing(1.25),
  borderRadius: '50%',
  backgroundColor: theme.palette.background.default,
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  '& span': {
    fontSize: theme.mixins.pxToRem(16),
    fontWeight: theme.typography.fontWeightBold
  }
}));

const WrapperButtonAddText = styled(Box)(({ theme }) => ({
  position: 'absolute',
  top: 16,
  right: 16
}));

const LineIconSetting = styled(LineIcon, {
  shouldForwardProp: props => props !== 'open'
})<{ open?: boolean }>(({ theme, open }) => ({
  marginBottom: theme.spacing(1),
  ...(open && {
    color: theme.palette.primary.main
  })
}));

const ContentWrapper = styled(Box, {
  name,
  slot: 'ContentWrapper'
})(({ theme }) => ({
  width: '100%',
  height: '100%',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  flex: 1,
  minHeight: 0,
  position: 'relative'
}));

const ImageUrl = styled('img', {
  name: 'PreviewDv',
  slot: 'Root'
})(({ theme }) => ({
  pointerEvents: 'none',
  objectFit: 'contain',
  maxWidth: 'unset'
}));

type FileType = 'video' | 'photo';

function Base() {
  const { i18n, eventCenter, useTheme, usePageParams, dispatch } = useGlobal();
  const pageParams = usePageParams();
  const [fontstyleOptions, setFontstyleOptions] = React.useState([]);
  const [nameFieldExpandLink, setNameFieldExpandLink] = React.useState(null);
  const [idEditText, setIdEditText] = React.useState(null);
  const [listText, setListText] = React.useState([]);
  const [fileUrl, setFileUrl] = React.useState();
  const [typeFile, setTypeFile] = React.useState<FileType>();
  const [item, setItem] = React.useState<any>({});
  const [openForm, setOpenForm] = React.useState(false);
  const [dataEvent, setDataEvent] = React.useState<any>();
  const isMinHeight = useMediaQuery('(max-height:667px)');

  const numberClickAdd = React.useRef(null);

  const context = useAddFormContext();
  const {
    checkSetStatus,
    filePhoto,
    uploading,
    setIsDirty,
    isSubmitting,
    setIsSubmitting
  } = context || {};

  const theme = useTheme();
  const [textInput, setTextInput] = React.useState('');
  const inputContainer = React.useRef();
  const contentRef = React.useRef<HTMLDivElement>();
  const containerImageRef = React.useRef<HTMLDivElement>();
  const inputDrafRef = React.useRef<HTMLDivElement>();
  const videoRef = React.useRef<any>();
  const fileRef = React.useRef<any>();
  const initRef = React.useRef<any>(true);
  const [muted, setMuted] = React.useState(true);

  const [sizeImg, setSizeImg] = React.useState<SizeImgProp>({
    width: 0,
    height: 0
  });
  const [progressFile, setProgressFile] = React.useState(0);
  const [lexicalState, setLexicalState] = React.useState<string>('');
  const editorRef = React.useRef<any>({});

  initStateCrop.imageSrc = fileUrl;
  const [stateImage, fire] = React.useReducer(reducerCrop, {
    ...initStateCrop
  });

  const [width, height] = useGetSizeContainer(containerImageRef);

  const { position, rotation, zoom, bound: boundImage, isDirty } = stateImage;

  React.useEffect(() => {
    if (!videoRef.current) return;

    if (isSubmitting) {
      videoRef.current?.pause();
    }
  }, [isSubmitting]);

  const onMediaLoad = React.useCallback(() => {
    if (!initRef.current || (!videoRef.current && !fileRef.current)) return;

    if (!width) return;

    const naturalWidth =
      fileRef.current?.naturalWidth || videoRef.current?.videoWidth || 0;
    const naturalHeight =
      fileRef.current?.naturalHeight || videoRef.current?.videoHeight || 0;

    const ratio = naturalWidth / width;
    const size = {
      width: parseInt(naturalWidth / ratio),
      height: parseInt(naturalHeight / ratio)
    };

    if (typeFile === 'video' && videoRef.current) {
      formRef.current?.setFieldValue(
        'duration',
        parseInt(videoRef.current?.duration)
      );
    }

    const position = { x: 0, y: (height - size.height) / 2 };

    setSizeImg(size);
    fire({
      type: 'setInitImage',
      payload: {
        width: size.width,
        height: size.height,
        widthContainer: width,
        heightContainer: height,
        position
      }
    });
    initRef.current = false;
  }, [width, typeFile, height]);

  React.useEffect(() => {
    if (!width) return;

    onMediaLoad();
  }, [onMediaLoad, width]);

  const itemSelect = listText.find(item => item.id === idEditText);

  const focusEditor = React.useCallback(() => {
    // updating open and focus at the same time cause bug: plugin editor not works
    setImmediate(() => editorRef.current?.editor?.focus());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (uploading) return;

    if (filePhoto) {
      const objectUrl = filePhoto?.temp_file
        ? filePhoto?.url
        : (URL.createObjectURL(filePhoto) as any);

      const type = isVideoType(filePhoto?.type) ? 'video' : 'photo';

      setTypeFile(type);

      setFileUrl(objectUrl);
    }
  }, [filePhoto, uploading]);

  React.useEffect(() => {
    if (uploading) {
      const fetchImageSource = async () => {
        const imageDataUrl = await readFile(filePhoto);

        dispatch({
          type: FETCH_PREVIEW_PHOTO,
          payload: {
            item: {
              source: imageDataUrl,
              file: filePhoto,
              type: 'photo',
              file_type: 'photo'
            }
          },
          meta: {
            onUploadProgress: (event: any) => {
              const progress = Math.round((event.loaded * 100) / event.total);

              if (progress > 99) return;

              setProgressFile(progress);
            },
            onParseFile: parseFile => {
              const { preUploadFile } = parseFile;

              context.setFilePhoto(preUploadFile);

              context.setUploading(false);
            },
            onError: () => {
              context.setUploading(false);
            }
          }
        });
      };
      fetchImageSource();
    }
  }, [filePhoto, uploading]);

  const formRef = React.useRef<any>();

  const pxToPersentScreen = (position, isNew = false) => {
    if (isEmpty(position)) return { x: 0, y: 0 };

    let positionX = position?.x;

    const rect = inputDrafRef?.current?.getBoundingClientRect();

    if (isNew && inputDrafRef?.current) {
      positionX = positionX - rect.width / 2;
    }

    const x = (positionX * 100) / width;
    const y = (position?.y * 100) / height;

    return {
      top: `${parseFloat(y?.toFixed(2))}%` || 0,
      left: `${parseFloat(x?.toFixed(2))}%` || 0
    };
  };

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const form = get(data, 'form');

      if (!isEqual(form, formRef.current)) {
        formRef.current = form;
      }
    });

    if (!formRef.current) return;

    const texts = listText.map(x =>
      omit(
        {
          ...x,
          textAlign: 'center',
          transform: {
            rotation: 0,
            scale: 1,
            position: pxToPersentScreen(x?.position, x?.isNew)
          }
        },
        KEY_TEXT_EXCLUDES
      )
    );
    formRef.current?.setFieldValue('file', filePhoto);

    formRef.current?.setFieldValue('type', typeFile);
    formRef.current?.setFieldValue('extra', {
      transform: {
        position: pxToPersentScreen(position),
        rotation,
        scale: zoom
      },
      storyHeight: height,
      texts,
      size: {
        width: sizeImg.width,
        height: sizeImg.height
      }
    });

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    filePhoto,
    position,
    rotation,
    zoom,
    width,
    height,
    typeFile,
    listText,
    sizeImg
  ]);

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const values = get(data, 'values');

      setDataEvent(data);
      const expand_link = get(
        data,
        'schema.elements.content.elements.basic.elements.expand_link'
      );
      setNameFieldExpandLink(expand_link?.name);

      const fontstyle_options = get(
        data,
        'schema.elements.content.elements.basic.elements.font_style.options'
      );

      setFontstyleOptions(fontstyle_options ?? []);

      if (isEmpty(values)) return;

      setItem(prev => ({ ...prev, ...values }));
    });

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onExit = () => {
    if (!idEditText) return;

    setListText(prev =>
      prev
        .filter(item => item.text)
        .map(item => {
          if (item.isNew) {
            const rect = inputDrafRef?.current?.getBoundingClientRect();

            const centerX = item?.position?.x - rect?.width / 2;
            const centerY = 150;

            const position = { x: centerX || 0, y: centerY || 0 };

            return { ...item, position, visible: true, isNew: false };
          }

          return { ...item, visible: true };
        })
    );
    setIdEditText(null);
    setTextInput('');

    setLexicalState('');
  };

  const handleClickTextItem = data => {
    setTextInput(data.text);
    setListText(prev =>
      prev.map(item => {
        if (item?.id === data?.id) return { ...item, visible: false };

        return item;
      })
    );
    setIdEditText(data.id);

    setLexicalState(escape(data.text));
  };

  const handleDeleteText = data => {
    setListText(prev => prev.filter(item => item.id !== data.id));
    onExit();
  };

  const handleOutsideClick = e => {
    if (inputContainer?.current && inputContainer.current?.contains(e?.target))
      return;

    onExit();
  };

  const updateInfoTextItem = data => {
    setListText(prev =>
      prev.map(item => {
        if (item?.id === idEditText) {
          return { ...item, ...data };
        }

        return item;
      })
    );
  };

  const updateExpandData = data => {
    setItem(prev => ({ ...prev, ...data }));
  };

  const handleChangeCompose = editorState => {
    setLexicalState(editorState);
    setTextInput(editorState);
  };

  React.useEffect(() => {
    if (idEditText) {
      setListText(prev =>
        prev.map(item => {
          if (item?.id === idEditText) {
            const rect = inputDrafRef?.current?.getBoundingClientRect();
            const widthText = rect?.width
              ? `${(rect.width / (width - 2)) * 100}%`
              : '100%';

            return {
              ...item,
              width: widthText,
              text: textInput,
              visible: false
            };
          }

          return item;
        })
      );
    }
  }, [
    idEditText,
    textInput,
    width,
    itemSelect?.fontSize,
    itemSelect?.fontFamily
  ]);

  const handleBack = () => {
    checkSetStatus();
  };

  const onSuccess = React.useCallback(() => {
    setIsSubmitting(false);
    setIsDirty(false);
  }, []);

  const getValues = item => {
    const values = item || {};

    const texts = listText.map(x =>
      omit(
        {
          ...x,
          textAlign: 'center',
          transform: {
            rotation: 0,
            scale: 1,
            position: pxToPersentScreen(x?.position, x?.isNew)
          }
        },
        KEY_TEXT_EXCLUDES
      )
    );

    return {
      ...values,
      file: filePhoto,
      type: typeFile,
      extra: {
        transform: {
          position: pxToPersentScreen(position),
          rotation,
          scale: zoom
        },
        storyHeight: height,
        texts,
        size: {
          width: sizeImg.width,
          height: sizeImg.height
        }
      }
    };
  };

  const handleSubmit = () => {
    const values = getValues({ ...dataEvent?.values, ...item });

    // Library modern-screenshot isn't working on mobile, should must cature video to image
    if (typeFile === 'video') {
      captureVideo(videoRef.current);
    }

    onExit();
    setIsSubmitting(true);
    dispatch({
      type: 'story/submitFormAdd',
      payload: {
        ...dataEvent,
        formSchema: dataEvent?.schema,
        values,
        pageParams
      },
      meta: { onSuccess }
    });
  };

  const handleOpenForm = () => {
    setOpenForm(prev => !prev);
    onExit();
  };

  const handleAddText = () => {
    if (isSubmitting) return;

    const key = uniqueId('add_text');

    if (isEqual(numberClickAdd.current, key)) return;

    numberClickAdd.current = key;
    setIdEditText(key);

    const existItem = listText.find(item => item.id === key);

    if (!existItem) {
      const rect = contentRef.current?.getBoundingClientRect();

      const centerX = rect.width / 2;
      const centerY = 150;

      const position = { x: centerX, y: centerY };

      setListText([
        ...listText,
        {
          id: key,
          text: '',
          visible: false,
          fontFamily: fontstyleOptions?.[0]?.value || DEFAULT_FONTSTYLE,
          textAlign: 'center',
          fontSize: DEFAULT_FONTSIZE,
          width: '100%',
          color: DEFAULT_COLOR,
          isNew: true,
          position
        }
      ]);

      focusEditor();
    }
  };

  const widthContentWrapper = React.useMemo(() => {
    if (isMinHeight) return 'auto';

    if (window.screen.width < height) return width;

    return WidthInteractionLandscape;
  }, [isMinHeight, height, width]);

  const widthFooter = React.useMemo(() => {
    if (!width && !height) return widthContentWrapper;

    if (!isMinHeight || window.screen.width < height) return width;

    return WidthInteractionLandscape;
  }, [isMinHeight, height, width, widthContentWrapper]);

  if (isNil(fileUrl) && !uploading) return null;

  const widthImg = sizeImg?.width ? sizeImg.width * zoom : 0;
  const heightImg = sizeImg?.height ? sizeImg.height * zoom : 0;
  const offset = (heightImg - widthImg) / 2;
  const init = !width && !height;

  const handleMuted = () => {
    setMuted(prev => !prev);
  };

  return (
    <RootStyled>
      <FontComponent />
      {init || isSubmitting ? <BlockLoading /> : null}
      {uploading ? <BlockUploadingFile progressFile={progressFile} /> : null}
      <ContentWrapper
        style={{
          width: widthContentWrapper,
          height: height ? height : '100%'
        }}
      >
        <ItemWrapper>
          <StoryImageContainer
            init={init}
            height={height}
            width={width}
            ref={containerImageRef}
          >
            <ButtonBackIcon onClick={handleBack}>
              <LineIcon icon="ico-angle-left" />
            </ButtonBackIcon>
            {typeFile === 'video' && (
              <ButtonMutedIcon size="medium" onClick={handleMuted}>
                <LineIcon
                  icon={muted ? 'ico-volume-del' : 'ico-volume-increase'}
                />
              </ButtonMutedIcon>
            )}
            <StoryImage ref={contentRef} id={IMAGE_PREVIEW_BACKGROUND}>
              {typeFile === 'photo' ? (
                <BackgroundBlur
                  style={{
                    backgroundImage: `url(${fileUrl})`
                  }}
                />
              ) : (
                <BackgroundBlur type={typeFile}>
                  <video
                    src={fileUrl}
                    draggable={false}
                    controls={false}
                    autoPlay={false}
                    playsInline
                    muted
                    id="videoThumbMobileId"
                    style={{
                      borderRadius: theme.shape.borderRadius,
                      width: '100%',
                      height: '100%',
                      border: 0,
                      ...(isSubmitting && { visibility: 'hidden' })
                    }}
                  />
                </BackgroundBlur>
              )}
              <Draggable
                bounds={
                  rotation % 4 === 0
                    ? boundImage
                    : {
                        top: boundImage.top - offset,
                        left: boundImage.left + offset,
                        right: boundImage.right + offset,
                        bottom: boundImage.bottom - offset
                      }
                }
                key={`${fileUrl}${zoom}${rotation}`}
                position={position}
                positionOffset={
                  rotation % 4 === 0
                    ? undefined
                    : {
                        x: 0 - offset,
                        y: offset
                      }
                }
                onDrag={(e: Event, data: DraggableData) => {
                  fire({
                    type: 'setDrag',
                    payload: { position: data }
                  });
                }}
              >
                <WrapperImage
                  sx={{
                    width: rotation % 4 === 0 ? widthImg : heightImg,
                    height: rotation % 4 === 0 ? heightImg : widthImg
                  }}
                >
                  {typeFile === 'photo' ? (
                    <ImageUrl
                      key={`${fileUrl}${zoom}`}
                      src={fileUrl}
                      ref={fileRef}
                      onLoad={onMediaLoad}
                      style={{
                        transform: mappingRotate[rotation],
                        transformOrigin: '0 0',
                        width: widthImg,
                        height: heightImg,
                        position: 'absolute',
                        left: 0,
                        top: 0
                      }}
                    />
                  ) : (
                    <>
                      <video
                        autoPlay={!isSubmitting}
                        loop={!isSubmitting}
                        muted={muted}
                        playsInline
                        src={fileUrl}
                        ref={videoRef}
                        id="videoMobileId"
                        onLoadedMetadata={onMediaLoad}
                        className={isSubmitting ? 'srOnly' : ''}
                        style={{
                          transform: mappingRotate[rotation],
                          transformOrigin: '0 0',
                          width: widthImg,
                          height: heightImg,
                          position: 'absolute',
                          left: 0,
                          top: 0,
                          ...(isSubmitting && { visibility: 'hidden' })
                        }}
                        controls={false}
                      />
                      <div
                        id="captureVideoId"
                        className={isSubmitting ? '' : 'srOnly'}
                        style={{
                          transform: mappingRotate[rotation],
                          transformOrigin: '0 0',
                          width: widthImg,
                          height: heightImg,
                          position: 'absolute',
                          left: 0,
                          top: 0
                        }}
                      />
                    </>
                  )}
                </WrapperImage>
              </Draggable>
              <InputContent
                style={{
                  display: idEditText ? 'inline-block' : 'none',
                  color: itemSelect?.color ?? DEFAULT_COLOR,
                  fontSize: itemSelect?.fontSize ?? DEFAULT_FONTSIZE
                }}
                fontFamily={itemSelect?.fontFamily}
                onClick={handleOutsideClick}
              >
                <EditorBlockStyled ref={inputContainer}>
                  <StoryEditor
                    onChange={handleChangeCompose}
                    value={lexicalState}
                    editorRef={editorRef}
                    placeholder={i18n.formatMessage({ id: 'start_typing' })}
                    sx={{
                      border: 0,
                      padding: '6px 12px',
                      background: 'none',
                      overflow: 'visible',
                      minHeight: '32px'
                    }}
                    sxInner={{
                      '& .editor-placeholder': {
                        overflow: 'inherit',
                        left: 0,
                        right: 0,
                        justifyContent: 'center',
                        color: '#fff'
                      }
                    }}
                  />
                </EditorBlockStyled>
              </InputContent>
              {textInput ? (
                <Draggable bounds="parent" position={{ x: 0, y: 0 }}>
                  <TextContent
                    ref={inputDrafRef}
                    fontFamily={itemSelect?.fontFamily}
                    style={{
                      visibility: 'hidden',
                      fontSize: itemSelect?.fontSize ?? DEFAULT_FONTSIZE,
                      border: 'none'
                    }}
                  >
                    <Box sx={{ whiteSpace: 'pre-line' }}>{textInput}</Box>
                  </TextContent>
                </Draggable>
              ) : null}
              {listText?.length ? (
                <>
                  {listText
                    .filter(textItem => textItem.visible)
                    .map(item => (
                      <TextItemDraggable
                        key={item?.id}
                        item={item}
                        setListText={setListText}
                        widthContainer={width}
                        heightContainer={height}
                        onSelectItemText={handleClickTextItem}
                      />
                    ))}
                </>
              ) : null}
            </StoryImage>
            <WrapperButtonAddText>
              <AddTextStyled onClick={handleAddText}>
                <LineIcon icon="ico-plus" />
              </AddTextStyled>
            </WrapperButtonAddText>
            {item?.expand_link && <SeeMoreLink link={item?.expand_link} />}
          </StoryImageContainer>

          {idEditText ? (
            <MenuTextItem
              itemSelect={itemSelect}
              handleDeleteText={handleDeleteText}
              item={item}
              updateItem={updateInfoTextItem}
              optionFontStyle={fontstyleOptions}
              nameFieldExpandLink={nameFieldExpandLink}
              updateExpandData={updateExpandData}
            />
          ) : null}
        </ItemWrapper>
        {isSubmitting ? null : (
          <FormStoryMobile
            open={openForm}
            setOpen={setOpenForm}
            isMinHeight={isMinHeight}
          />
        )}
      </ContentWrapper>
      <CropImage open={isDirty} fire={fire} state={stateImage} />
      <FooterStyled isMinHeight={isMinHeight} width={widthFooter}>
        <PrivacyStyled onClick={handleOpenForm}>
          <LineIconSetting icon="ico-gear-o" color="primary" open={openForm} />
          <Typography component={'span'} variant="body2">
            {i18n.formatMessage({ id: 'privacy_settings' })}
          </Typography>
        </PrivacyStyled>
        <ButtonSubmit>
          <MuiButton
            loading={isSubmitting}
            disabled={isSubmitting}
            onClick={handleSubmit}
            variant="contained"
            color="primary"
            size="medium"
            type="submit"
            role="button"
          >
            {i18n.formatMessage({ id: 'create' })}
          </MuiButton>
        </ButtonSubmit>
      </FooterStyled>
    </RootStyled>
  );
}

export default Base;
