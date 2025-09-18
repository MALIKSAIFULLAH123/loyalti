import { FETCH_PREVIEW_PHOTO, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { Box, Paper, styled } from '@mui/material';
import * as React from 'react';
import { escape, get, isEmpty, isEqual, isNil, omit } from 'lodash';
import {
  DEFAULT_COLOR,
  DEFAULT_FONTSIZE,
  DEFAULT_FONTSTYLE,
  HEIGHT_RATIO_SIZE,
  IMAGE_PREVIEW_BACKGROUND,
  KEY_TEXT_EXCLUDES,
  StoryPreviewChanged
} from '@metafox/story/constants';
import { ClickOutsideListener, Popper } from '@metafox/ui';
import useAddFormContext, { useGetSizeContainer } from '@metafox/story/hooks';
import loadable from '@loadable/component';
import { DraggableData } from 'react-draggable';
import MenuTextItem from './MenuTextItem';
import { isVideoType } from '@metafox/utils';
import {
  BlockLoading,
  BlockUploadingFile,
  CropImage,
  FontComponent,
  SizeImgProp,
  initStateCrop,
  reducerCrop
} from '@metafox/story/components';
import { mappingRotate, readFile } from '@metafox/story/utils';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';
import StoryEditor from '../Composer/StoryEditor';
import TextItemDraggable from './TextItemDraggable';
import { TextItemProps } from '@metafox/story/types';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const name = 'StoryReview';

const ItemWrapper = styled('div', {
  name,
  slot: 'root'
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

const StoryImageContainer = styled('div', {
  name,
  slot: 'StoryImageContainer',
  shouldForwardProp: props =>
    props !== 'width' && props !== 'height' && props !== 'init'
})<{ height?: number; width?: number; init?: boolean }>(
  ({ theme, height, width, init }) => ({
    height: '100%',
    width: width ? width : '390px',
    position: 'relative',
    overflow: 'hidden',
    border: theme.mixins.border('secondary'),
    borderColor: '#fff',
    borderRadius: theme.shape.borderRadius,
    ...(init && {
      opacity: 0.1,
      borderWidth: 0
    })
  })
);

const StoryImage = styled('div', {
  name,
  slot: 'StoryImage'
})(({ theme }) => ({
  height: '100%',
  width: '100%'
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
    top: '50px',
    right: 0
  })
);
const InputContent = styled('div', {
  name,
  slot: 'InputContent',
  shouldForwardProp: prop => prop !== 'fontFamily' && prop !== 'fontSize'
})<{ fontFamily?: string; fontSize?: string | number }>(
  ({ theme, fontFamily, fontSize }) => ({
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
    fontSize: fontSize ? `${fontSize}px !important` : '22px !important',
    wordBreak: 'break-word',
    wordWrap: 'break-word',
    color: '#fff',
    '& a': {
      color: '#fff'
    },
    ...(fontFamily && {
      fontFamily
    })
  })
);

export const TextContent = styled('div', {
  name,
  slot: 'TextContent',
  shouldForwardProp: prop =>
    prop !== 'fontFamily' && prop !== 'position' && prop !== 'fontSize'
})<{ fontFamily?: string; position?: boolean; fontSize?: string | number }>(
  ({ theme, fontFamily, position, fontSize }) => ({
    maxHeight: '100%',
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    right: 0,
    textAlign: 'center',
    zIndex: 1,
    fontWeight: theme.typography.fontWeightRegular,
    fontSize: fontSize ? `${fontSize}px !important` : '22px !important',
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
      borderColor: '#fff',
      '& button': {
        opacity: 1
      }
    }
  })
);

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  cursor: 'move'
}));

const ImageUrl = styled('img', {
  name: 'PreviewDv',
  slot: 'Root'
})(({ theme }) => ({
  pointerEvents: 'none',
  objectFit: 'contain',
  maxWidth: 'unset'
}));

const PopperWrapper = styled(Popper, {
  name: 'PopperWrapper'
})<{}>(({ theme }) => ({
  minWidth: '180px',
  width: 'auto',
  maxHeight: '70vh',
  maxWidth: 300
}));

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(2)
}));

type FileType = 'video' | 'photo';

export default function StoryReview(props: any) {
  const { i18n, eventCenter, useTheme, dispatch } = useGlobal();
  const theme = useTheme();
  const [item, setItem] = React.useState<any>({});
  const [fileUrl, setFileUrl] = React.useState();
  const [typeFile, setTypeFile] = React.useState<FileType>();
  const [idEditText, setIdEditText] = React.useState(null);
  const numberClickAdd = React.useRef(null);
  const [textInput, setTextInput] = React.useState('');
  const [listText, setListText] = React.useState<TextItemProps[] | []>([]);
  const inputContainer = React.useRef();
  const contentRef = React.useRef<HTMLDivElement>();
  const containerImageRef = React.useRef<HTMLDivElement>();
  const paperRef = React.useRef();
  const videoRef = React.useRef<any>();
  const fileRef = React.useRef<any>();
  const inputDrafRef = React.useRef<HTMLDivElement>();
  const [fontstyleOptions, setFontstyleOptions] = React.useState([]);
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const [sizeImg, setSizeImg] = React.useState<SizeImgProp>({
    width: 0,
    height: 0
  });
  const [dimensionImage, setDimensionImage] = React.useState<SizeImgProp>();
  const [mediaLoaded, setMediaLoaded] = React.useState(false);

  const [progressFile, setProgressFile] = React.useState(0);

  initStateCrop.imageSrc = fileUrl;
  const [stateImage, fire] = React.useReducer(reducerCrop, {
    ...initStateCrop
  });

  const [width, height, onResize] = useGetSizeContainer(containerImageRef);
  const x = containerImageRef?.current?.getBoundingClientRect();

  React.useEffect(() => {
    onResize();
  }, [onResize, x]);
  const { position, rotation, zoom, bound: boundImage, isDirty } = stateImage;

  const contentRect = contentRef.current?.getBoundingClientRect();

  const ratio = contentRect?.height / HEIGHT_RATIO_SIZE;

  const [lexicalState, setLexicalState] = React.useState<string>('');
  const editorRef = React.useRef<any>({});
  const formRef = React.useRef<any>();

  React.useEffect(() => {
    if (!videoRef.current) return;

    // add props muted on dom
    videoRef.current.defaultMuted = true;

    if (isSubmitting) {
      videoRef.current?.pause();
    }
  }, [isSubmitting]);
  const onMediaLoad = () => {
    setMediaLoaded(true);
  };

  const calculateSize = () => {
    if (!videoRef.current && !fileRef.current) return;

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

    setDimensionImage({ width: naturalWidth, height: naturalHeight });
    setSizeImg(size);
    fire({
      type: 'setInitImage',
      payload: {
        width: size?.width,
        height: size?.height,
        widthContainer: width,
        heightContainer: height,
        position
      }
    });
  };

  React.useEffect(() => {
    if (!width || !mediaLoaded) return;

    calculateSize();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [width, mediaLoaded]);
  const itemSelect = listText.find(item => item.id === idEditText);

  const context = useAddFormContext();

  const { uploading, setInit } = context || {};

  const focusEditor = React.useCallback(() => {
    // updating open and focus at the same time cause bug: plugin editor not works
    setImmediate(() => editorRef.current?.editor?.focus());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (uploading || isNil(fileUrl)) return;

    setInit(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [uploading, fileUrl]);

  React.useEffect(() => {
    if (uploading) return;

    if (context?.filePhoto) {
      const objectUrl = context?.filePhoto?.temp_file
        ? context?.filePhoto?.url
        : (URL.createObjectURL(context.filePhoto) as any);

      const type = isVideoType(context.filePhoto?.type) ? 'video' : 'photo';

      setTypeFile(type);

      setFileUrl(objectUrl);
    }
  }, [context?.filePhoto, uploading]);

  React.useEffect(() => {
    if (uploading) {
      const fetchImageSource = async () => {
        const imageDataUrl = await readFile(context?.filePhoto);

        dispatch({
          type: FETCH_PREVIEW_PHOTO,
          payload: {
            item: {
              source: imageDataUrl,
              file: context?.filePhoto,
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
  }, [context?.filePhoto, uploading]);

  const pxToPersentScreen = (position, isNew = false) => {
    if (isEmpty(position)) return { x: 0, y: 0 };

    let positionX = position?.x;

    const rect = inputDrafRef?.current?.getBoundingClientRect();

    if (isNew && inputDrafRef?.current) {
      positionX = positionX - rect?.width / 2;
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

      formRef.current = form;
    });

    if (formRef.current) {
      const texts = listText.map(x =>
        omit(
          {
            ...x,
            transform: {
              rotation: 0,
              scale: 1,
              position: pxToPersentScreen(x?.position, x?.isNew)
            }
          },
          KEY_TEXT_EXCLUDES
        )
      );

      formRef.current?.setFieldValue('file', context.filePhoto);

      formRef.current?.setFieldValue('type', typeFile);
      formRef.current?.setFieldValue('extra', {
        transform: {
          position: pxToPersentScreen(position),
          rotation,
          scale: (sizeImg?.width * zoom) / dimensionImage?.width
        },
        storyHeight: height,
        texts,
        size: {
          width: dimensionImage?.width,
          height: dimensionImage?.height
        }
      });
    }

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    context.filePhoto,
    position,
    rotation,
    zoom,
    width,
    height,
    typeFile,
    listText,
    dimensionImage
  ]);

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const values = get(data, 'values');
      const form = get(data, 'form');

      setIsSubmitting(form?.isSubmitting || false);

      const fontstyle_options = get(
        data,
        'schema.elements.content.elements.basic.elements.AddTextStyle.options'
      );
      setFontstyleOptions(fontstyle_options ?? []);

      if (isEmpty(values)) return;

      if (
        !isNil(values.add_text) &&
        !isEqual(numberClickAdd.current, values.add_text)
      ) {
        numberClickAdd.current = values.add_text;
        setIdEditText(values.add_text);

        const existItem = listText.find(item => item.id === values.add_text);

        if (!existItem) {
          const rect = contentRef?.current?.getBoundingClientRect();

          const centerX = rect.width / 2;
          const centerY = 100;

          const position = { x: centerX, y: centerY };

          const data = [
            ...listText,
            {
              id: values.add_text,
              text: '',
              visible: false,
              fontFamily: fontstyle_options?.[0]?.value || DEFAULT_FONTSTYLE,
              fontSize: DEFAULT_FONTSIZE,
              textAlign: 'center',
              color: DEFAULT_COLOR,
              width: '100%',
              isNew: true,
              position,
              rotation: 0,
              scale: 1
            }
          ];

          setListText(data);

          focusEditor();
        }
      }

      setItem(values);
    });

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listText]);

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

  const onExit = () => {
    setIdEditText(null);
    setTextInput('');
    setListText(prev =>
      prev
        .filter(item => item.text)
        .map(item => {
          const rect = inputDrafRef?.current?.getBoundingClientRect();

          if (item?.isNew) {
            const centerX = item.position.x - rect?.width / 2;
            const centerY = 100;

            const position = { x: centerX, y: centerY };

            return {
              ...item,
              position,
              visible: true,
              isNew: false
            };
          }

          return { ...item, visible: true };
        })
    );

    setLexicalState('');
  };

  const handleOutsideClick = e => {
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

    focusEditor();
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    idEditText,
    textInput,
    width,
    itemSelect?.fontSize,
    itemSelect?.fontFamily
  ]);

  if ((isNil(fileUrl) && !uploading) || isEmpty(item)) return null;

  const widthImg = sizeImg?.width ? sizeImg.width * zoom : 0;
  const heightImg = sizeImg?.height ? sizeImg.height * zoom : 0;
  const offset = (heightImg - widthImg) / 2;
  const init = !width && !height;

  return (
    <Block testid={`preview ${item?.resource_name}`}>
      <FontComponent />
      {init || isSubmitting ? <BlockLoading /> : null}
      {uploading ? <BlockUploadingFile progressFile={progressFile} /> : null}
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: 'preview' })}</BlockTitle>
      </BlockHeader>
      <BlockContent>
        <ItemWrapper>
          <StoryImageContainer
            init={init}
            height={height}
            width={width}
            ref={containerImageRef}
          >
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
                    muted
                    playsInline
                    style={{
                      borderRadius: theme.shape.borderRadius,
                      width: '100%',
                      height: '100%',
                      border: 0
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
                    <video
                      autoPlay={!isSubmitting}
                      loop={!isSubmitting}
                      // muted
                      playsInline
                      src={fileUrl}
                      ref={videoRef}
                      onLoadedMetadata={onMediaLoad}
                      style={{
                        transform: mappingRotate[rotation],
                        transformOrigin: '0 0',
                        width: widthImg,
                        height: heightImg,
                        position: 'absolute',
                        left: 0,
                        top: 0
                      }}
                      controls={false}
                    />
                  )}
                </WrapperImage>
              </Draggable>
              <InputContent
                style={{
                  display: idEditText ? 'inline-block' : 'none',
                  color: itemSelect?.color ?? DEFAULT_COLOR
                }}
                fontFamily={itemSelect?.fontFamily}
                fontSize={(itemSelect?.fontSize ?? DEFAULT_FONTSIZE) * ratio}
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
                    fontSize={
                      (itemSelect?.fontSize ?? DEFAULT_FONTSIZE) * ratio
                    }
                    style={{
                      visibility: 'hidden',
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
                        ratio={ratio}
                        onSelectItemText={handleClickTextItem}
                      />
                    ))}
                </>
              ) : null}
              {idEditText ? (
                <ClickOutsideListener
                  excludeRef={inputContainer}
                  onClickAway={handleOutsideClick}
                  mouseEvent="onMouseUp"
                >
                  <PopperWrapper
                    id={'menu-text-item'}
                    data-testid={'menu-text-item'}
                    open={Boolean(idEditText)}
                    anchorEl={contentRef.current}
                    placement="right"
                  >
                    <PaperMenu ref={paperRef}>
                      <MenuTextItem
                        optionFontStyle={fontstyleOptions}
                        item={itemSelect}
                        updateItem={updateInfoTextItem}
                      />
                    </PaperMenu>
                  </PopperWrapper>
                </ClickOutsideListener>
              ) : null}
            </StoryImage>
            {!formRef.current?.errors?.expand_link && item?.expand_link && (
              <SeeMoreLink link={item?.expand_link} />
            )}
          </StoryImageContainer>
          <CropImage open={isDirty} fire={fire} state={stateImage} />
        </ItemWrapper>
      </BlockContent>
    </Block>
  );
}
