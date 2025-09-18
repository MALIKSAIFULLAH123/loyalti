import { useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { Box, styled } from '@mui/material';
import * as React from 'react';
import { escape, get, isEmpty } from 'lodash';
import {
  DEFAULT_COLOR,
  DEFAULT_FONTSIZE,
  HEIGHT_RATIO_SIZE,
  IMAGE_PREVIEW_BACKGROUND,
  StoryPreviewChanged
} from '@metafox/story/constants';
import { HtmlViewerWrapper } from '@metafox/ui';
import HtmlViewer from '@metafox/html-viewer';
import useAddFormContext, { useGetSizeContainer } from '@metafox/story/hooks';
import {
  BlockLoading,
  FontComponent,
  SizeImgProp
} from '@metafox/story/components';
import loadable from '@loadable/component';
import SeeMoreLink from '@metafox/story/components/SeeMoreButton/SeeMoreButton';

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
  display: 'flex',
  justifyContent: 'center',
  borderRadius: theme.shape.borderRadius
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
  border: theme.mixins.border('secondary'),
  borderColor: '#fff',
  borderRadius: theme.shape.borderRadius
}));

const StoryImage = styled('div', {
  name,
  slot: 'StoryImage'
})(({ theme }) => ({
  height: '100%',
  width: '100%'
}));

const TextContent = styled('div', {
  name,
  slot: 'TextContent',
  shouldForwardProp: prop => prop !== 'fontFamily' && prop !== 'fontSize'
})<{ fontFamily?: string; fontSize?: number | string }>(
  ({ theme, fontFamily, fontSize }) => ({
    overflow: 'hidden',
    position: 'absolute',
    left: 0,
    top: 0,
    right: 0,
    bottom: 0,
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
    lineHeight: 'normal',
    '& a': {
      color: '#fff'
    },
    ...(fontFamily && {
      fontFamily
    })
  })
);

const ImageUrl = styled('img', {
  name,
  slot: 'ImageUrl'
})(({ theme }) => ({
  pointerEvents: 'none',
  height: '100%',
  width: '100%'
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

export default function StoryReview(props: any) {
  const { i18n, eventCenter } = useGlobal();
  const [item, setItem] = React.useState<any>({});
  const containerImageRef = React.useRef<HTMLDivElement>();
  const contentRef = React.useRef<HTMLDivElement>();
  const [isSubmitting, setIsSubmitting] = React.useState(false);
  const fileRef = React.useRef<any>();
  const [sizeImg, setSizeImg] = React.useState<SizeImgProp>({
    width: 0,
    height: 0
  });
  const [dimensionImage, setDimensionImage] = React.useState<SizeImgProp>();
  const [position, setPosition] = React.useState({ x: 0, y: 0 });

  const { background, text, font_style: fontFamily, size } = item || {};

  const context = useAddFormContext();

  const { setInit } = context || {};

  const [width, height] = useGetSizeContainer(containerImageRef);

  const formRef = React.useRef<any>();
  const textRef = React.useRef<any>();

  const contentRect = contentRef.current?.getBoundingClientRect();

  const ratio = contentRect?.height / HEIGHT_RATIO_SIZE;

  const pxToPersentText = () => {
    const textRect = textRef.current?.getBoundingClientRect();

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

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const values = get(data, 'values');
      const form = get(data, 'form');

      setIsSubmitting(form?.isSubmitting || false);

      if (isEmpty(values)) return;

      const backgroundOptions = get(
        data,
        'schema.elements.content.elements.basic.elements.background_id.options'
      );

      const background = backgroundOptions.find(
        item => item.id === values.background_id
      )?.value;

      setItem({ ...values, background });
    });

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    const token = eventCenter.on(StoryPreviewChanged, data => {
      const form = get(data, 'form');

      formRef.current = form;
    });

    if (formRef.current) {
      const textRect = textRef.current?.getBoundingClientRect();
      const widthText = textRect?.width
        ? `${(textRect.width / (width - 2)) * 100}%`
        : '100%';

      const texts = [
        {
          text,
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

      formRef.current?.setFieldValue('extra', {
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
      });
    }

    return () => eventCenter.off(StoryPreviewChanged, token);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    height,
    text,
    fontFamily,
    size,
    sizeImg,
    dimensionImage,
    position,
    width,
    textRef.current
  ]);

  React.useEffect(() => {
    setInit(true);
  }, []);

  if (isEmpty(item)) return null;

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

  const widthImg = sizeImg?.width ? sizeImg.width : '100%';
  const heightImg = sizeImg?.height ? sizeImg.height : '100%';

  return (
    <Block testid={`preview ${item?.resource_name}`}>
      <FontComponent />
      {isSubmitting ? <BlockLoading /> : null}
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: 'preview' })}</BlockTitle>
      </BlockHeader>
      <BlockContent>
        <ItemWrapper ref={containerImageRef}>
          <StoryImageContainer height={height} width={width}>
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

              <TextContent
                fontFamily={fontFamily}
                fontSize={(size || DEFAULT_FONTSIZE) * ratio}
              >
                <Box ref={textRef}>
                  <HtmlViewerWrapper mt={0} sx={{ whiteSpace: 'normal' }}>
                    <HtmlViewer
                      html={
                        escape(text) ||
                        i18n.formatMessage({ id: 'start_typing' })
                      }
                    />
                  </HtmlViewerWrapper>
                </Box>
              </TextContent>
            </StoryImage>
          </StoryImageContainer>
          {!formRef.current?.errors?.expand_link && item?.expand_link && (
            <SeeMoreLink link={item?.expand_link} />
          )}
        </ItemWrapper>
      </BlockContent>
    </Block>
  );
}
