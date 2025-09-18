import { useGlobal } from '@metafox/framework';
import {
  DEFAULT_COLOR,
  DEFAULT_FONTSIZE,
  HEIGHT_RATIO_SIZE,
  IMAGE_PREVIEW_BACKGROUND,
  StoryPreviewChanged,
  WidthInteractionLandscape
} from '@metafox/story/constants';
import useAddFormContext, { useGetSizeContainer } from '@metafox/story/hooks';
import { Box, Button, Typography, styled, useMediaQuery } from '@mui/material';
import { get, isEmpty, isEqual, omit } from 'lodash';
import React from 'react';
import MuiButton from '@mui/lab/LoadingButton';
import MenuTextItem from './MenuTextItem';
import {
  BlockLoading,
  FontComponent,
  FormStoryMobile,
  SizeImgProp
} from '@metafox/story/components';
import { LineIcon } from '@metafox/ui';
import loadable from '@loadable/component';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';
import StoryEditor from '../Composer/StoryEditor';
import htmlToText from '../Composer/Lexical/utils/htmlToText';

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
  slot: 'root'
})(({ theme }) => ({
  position: 'relative',
  margin: 'auto',
  height: 'calc(100%)',
  alignItems: 'center',
  display: 'flex',
  justifyContent: 'center',
  borderRadius: theme.shape.borderRadius,
  flex: 1,
  minHeight: 0
}));

const StoryImageContainer = styled('div', {
  name,
  slot: 'StoryImageContainer',
  shouldForwardProp: props => props !== 'width' && props !== 'height'
})<{ height?: number; width?: number }>(({ theme, height, width }) => ({
  height: height ? height : '100%',
  width: width ? width : '390px',
  position: 'relative',
  overflow: 'hidden',
  borderRadius: theme.shape.borderRadius
}));

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

const StoryImage = styled('div', {
  name,
  slot: 'StoryImage'
})(({ theme }) => ({
  height: '100%',
  width: '100%'
}));

const EditorBlockStyled = styled('div', { name, slot: 'EditorBlockStyled' })(
  ({ theme }) => ({
    '& .editor-input': {
      minWidth: '16px'
    }
  })
);
const InputContent = styled('div', {
  name,
  slot: 'InputContent',
  shouldForwardProp: prop =>
    prop !== 'fontFamily' && prop !== 'fontSize' && prop !== 'width'
})<{
  fontFamily?: string;
  fontSize?: string | number;
  width?: number | string;
}>(({ theme, fontFamily, fontSize, width }) => ({
  height: '100%',
  overflow: 'hidden',
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
  fontSize: fontSize ? `${fontSize}px !important` : '28px !important',
  wordBreak: 'break-word',
  wordWrap: 'break-word',
  color: '#fff',
  '& a': {
    color: '#fff'
  },
  ...(fontFamily && {
    fontFamily
  }),
  '& .editor-placeholder': {
    overflow: 'inherit',
    justifyContent: 'center',
    position: 'absolute',
    top: '50%',
    left: '50%',
    transform: 'translate(-50%, -50%)',
    color: '#fff',
    height: '100%',
    width: width || '100vh'
  }
}));

const FooterStyled = styled(Box, {
  name,
  slot: 'FooterStyled',
  shouldForwardProp: props => props !== 'width' && props !== 'isMinHeight'
})<{ width?: any; isMinHeight?: boolean }>(({ theme, width, isMinHeight }) => ({
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

const BackgroundBlur = styled('div', {
  name,
  slot: 'BackgroundBlur'
})<{ type?: any }>(({ theme }) => ({
  filter: 'blur(30px)',
  WebkitFilter: 'blur(30px)',
  overflow: 'hidden',
  padding: theme.spacing(1.5),
  height: '100%',
  width: '100%',
  backgroundRepeat: 'no-repeat',
  backgroundSize: 'cover',
  backgroundPosition: 'center',
  transform: 'scale(2)'
}));

const WrapperImage = styled(Box, { name })(({ theme }) => ({
  position: 'absolute',
  top: 0,
  cursor: 'move'
}));

const ImageUrl = styled('img', {
  name,
  slot: 'ImageUrl'
})(({ theme }) => ({
  pointerEvents: 'none',
  height: '100%',
  width: '100%'
}));

export function filterObject(obj, excludes = ['extra', 'text']) {
  const data: any = omit({ ...Object.assign({}, obj) }, excludes);

  return Object.fromEntries(Object.entries(data).filter(item => item));
}

function Base() {
  const { i18n, eventCenter, dispatch, usePageParams, toastBackend } =
    useGlobal();
  const pageParams = usePageParams();
  const [item, setItem] = React.useState<any>({});
  const [openForm, setOpenForm] = React.useState(false);
  const [dataEvent, setDataEvent] = React.useState<any>();
  const containerImageRef = React.useRef<HTMLDivElement>();
  const [fontstyleOptions, setFontstyleOptions] = React.useState([]);
  const [optionBackground, setOptionBackground] = React.useState([]);
  const [nameFieldExpandLink, setNameFieldExpandLink] = React.useState(null);
  const isMinHeight = useMediaQuery('(max-height:667px)');
  const [hiddenActionText, setHiddenActionText] =
    React.useState<boolean>(false);

  const { checkSetStatus, setIsDirty, isSubmitting, setIsSubmitting } =
    useAddFormContext();

  const firstData = React.useRef({});
  const firstUpdate = React.useRef(true);

  const [width, height] = useGetSizeContainer(containerImageRef);
  const initRef = React.useRef(false);

  const fileRef = React.useRef<any>();
  const textRef = React.useRef<any>();
  const contentRef = React.useRef<HTMLDivElement>();

  const [sizeImg, setSizeImg] = React.useState<SizeImgProp>({
    width: 0,
    height: 0
  });
  const [dimensionImage, setDimensionImage] = React.useState<SizeImgProp>();
  const [position, setPosition] = React.useState({ x: 0, y: 0 });
  const [lexicalState, setLexicalState] = React.useState<string>('');
  const editorRef = React.useRef<any>({});

  const getCurrentText = lexicalState => {
    return htmlToText(lexicalState).trim();
  };

  const contentRect = contentRef.current?.getBoundingClientRect();

  const ratio = contentRect?.height / HEIGHT_RATIO_SIZE;

  const handleChangeCompose = editorState => {
    setLexicalState(editorState);

    setItem({ ...item, text: editorState });
  };

  const updateInfoTextItem = data => {
    setItem({ ...item, ...data });
  };

  const pxToPersentText = () => {
    const textRect = textRef.current?.getBoundingClientRect();
    const contentRect = contentRef.current?.getBoundingClientRect();

    const centerX = contentRect?.width / 2 - textRect?.width / 2;
    const centerY = contentRect?.height / 2 - textRect?.height / 2;

    const x = (centerX * 100) / width;
    const y = (centerY * 100) / height;

    return {
      top: `${parseFloat(y?.toFixed(2))}%` || 0,
      left: `${parseFloat(x?.toFixed(2))}%` || 0
    };
  };

  const pxToPersentImage = position => {
    if (isEmpty(position)) return { x: 0, y: 0 };

    const x = (position?.x * 100) / width;
    const y = (position?.y * 100) / height;

    return {
      top: `${parseFloat(y?.toFixed(2))}%` || 0,
      left: `${parseFloat(x?.toFixed(2))}%` || 0
    };
  };

  const { background, fontFamily, size } = item || {};

  React.useEffect(() => {
    setIsDirty(
      !!getCurrentText(lexicalState) ||
        !isEqual(firstData.current, filterObject(item))
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [item, lexicalState]);

  React.useEffect(() => {
    const textRect = textRef.current?.getBoundingClientRect();
    const widthText = textRect?.width
      ? `${(textRect.width / (width - 2)) * 100}%`
      : '100%';

    const texts = [
      {
        text: item?.text,
        fontFamily,
        fontSize: size,
        color: DEFAULT_COLOR,
        textAlign: 'center',
        width: widthText,
        transform: {
          rotation: 0,
          scale: 1,
          position: pxToPersentText()
        }
      }
    ];

    setItem({
      ...item,
      extra: {
        isBrowser: true,
        transform: {
          position: pxToPersentImage(position),
          rotation: 0,
          scale: sizeImg.width / dimensionImage?.width
        },
        storyHeight: height,
        texts,
        size: {
          width: dimensionImage?.width,
          height: dimensionImage?.height
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    height,
    item?.text,
    fontFamily,
    size,
    sizeImg,
    dimensionImage,
    position,
    width,
    textRef.current
  ]);

  const focusEditor = React.useCallback(() => {
    // updating open and focus at the same time cause bug: plugin editor not works
    setImmediate(() => editorRef.current?.editor?.focus());
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onMediaLoad = () => {
    if (!fileRef.current) return;

    const naturalWidth = fileRef.current?.naturalWidth || 0;
    const naturalHeight = fileRef.current?.naturalHeight || 0;

    const ratio = naturalWidth / width;
    const size = {
      width: parseInt(naturalWidth / ratio),
      height: parseInt(naturalHeight / ratio)
    };

    const position = { x: 0, y: (height - size.height) / 2 };

    setPosition(position);

    setDimensionImage({ width: naturalWidth, height: naturalHeight });
    setSizeImg(size);
  };

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const values = get(data, 'values');

      setDataEvent(data);

      if (isEmpty(values)) return;

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

      const backgroundOptions = get(
        data,
        'schema.elements.content.elements.basic.elements.background_id.options'
      );

      setOptionBackground(backgroundOptions);

      let background = backgroundOptions?.[0]?.value;

      let background_id = backgroundOptions?.[0]?.id;

      if (!initRef.current) {
        background =
          backgroundOptions.find(item => item.id === values.background_id)
            ?.value || background;

        background_id = values.background_id || background_id;

        initRef.current = true;
      }

      const _data = {
        ...Object.assign(
          {},
          {
            ...values,
            ...item,
            fontFamily: item?.fontFamily || values?.font_style,
            background: item?.background || background,
            background_id: item?.background_id || background_id
          }
        )
      };

      if (firstUpdate.current) {
        firstUpdate.current = false;
        firstData.current = filterObject(_data);
      }

      setItem(_data);
    });

    return () => {
      eventCenter.off(StoryPreviewChanged, token);
      initRef.current = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    item?.background,
    item?.background_id,
    item?.fontFamily,
    item?.size,
    item?.expand_link
  ]);

  const handleBack = () => {
    checkSetStatus();
  };

  const onSuccess = React.useCallback(() => {
    setIsSubmitting(false);
    setIsDirty(false);
  }, []);

  const handleSubmit = () => {
    if (isEmpty(item?.text)) {
      toastBackend.error(i18n.formatMessage({ id: 'text_required_field' }));

      return;
    }

    setIsSubmitting(true);
    dispatch({
      type: 'story/submitFormAdd',
      payload: {
        ...dataEvent,
        formSchema: dataEvent?.schema,
        values: item,
        pageParams
      },
      meta: { onSuccess }
    });
  };

  const handleOpenForm = () => {
    setOpenForm(prev => !prev);

    if (!openForm) {
      setHiddenActionText(true);
    } else {
      setHiddenActionText(false);
    }
  };

  const widthImg = sizeImg?.width ? sizeImg.width : '100%';
  const heightImg = sizeImg?.height ? sizeImg.height : '100%';
  const widthContentWrapper = React.useMemo(() => {
    if (isMinHeight) return 'auto';

    if (window.screen.width < height) return width;

    return WidthInteractionLandscape;
  }, [isMinHeight, height, width]);

  const widthFooter = React.useMemo(() => {
    if (!isMinHeight || window.screen.width < height) return width;

    return WidthInteractionLandscape;
  }, [isMinHeight, height, width]);

  return (
    <RootStyled>
      <FontComponent />
      {isSubmitting ? <BlockLoading /> : null}
      <ContentWrapper
        style={{
          width: widthContentWrapper,
          height: height ? height : '100%'
        }}
      >
        <ItemWrapper ref={containerImageRef}>
          <StoryImageContainer height={height} width={width}>
            <ButtonBackIcon onClick={handleBack}>
              <LineIcon icon="ico-angle-left" />
            </ButtonBackIcon>
            <StoryImage ref={contentRef} id={IMAGE_PREVIEW_BACKGROUND}>
              <BackgroundBlur
                style={{
                  backgroundImage: `url(${background})`
                }}
              />
              <Draggable key={background} position={position} disabled>
                <WrapperImage>
                  <ImageUrl
                    key={background}
                    ref={fileRef}
                    onLoad={onMediaLoad}
                    src={background}
                    style={{
                      width: widthImg,
                      height: heightImg
                    }}
                  />
                </WrapperImage>
              </Draggable>
              {hiddenActionText ? null : (
                <InputContent
                  width={width}
                  fontSize={(size || DEFAULT_FONTSIZE) * ratio}
                  fontFamily={fontFamily}
                  onClick={focusEditor}
                >
                  <EditorBlockStyled ref={textRef}>
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
                    />
                  </EditorBlockStyled>
                </InputContent>
              )}
            </StoryImage>
            <MenuTextItem
              item={item}
              updateItem={updateInfoTextItem}
              optionFontStyle={fontstyleOptions}
              optionBackground={optionBackground}
              hiddenActionText={hiddenActionText}
              setHiddenActionText={setHiddenActionText}
              nameFieldExpandLink={nameFieldExpandLink}
            />

            {item?.expand_link && <SeeMoreLink link={item?.expand_link} />}
          </StoryImageContainer>
        </ItemWrapper>
        {isSubmitting ? null : (
          <FormStoryMobile
            open={openForm}
            setOpen={setOpenForm}
            setHiddenActionText={setHiddenActionText}
            isMinHeight={isMinHeight}
          />
        )}
      </ContentWrapper>
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
