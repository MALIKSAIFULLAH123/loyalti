import React from 'react';
import loadable from '@loadable/component';
import { TextItemProps } from '@metafox/story/types';
import { DraggableData } from 'react-draggable';
import { LineIcon } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';
import { TextContent } from './Base';
import { DEFAULT_COLOR, DEFAULT_FONTSIZE } from '@metafox/story/constants';
import { Box, IconButton, styled } from '@mui/material';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const name = 'TextItemDraggable';

const IconButtonStyled = styled(IconButton, { name })(({ theme }) => ({
  position: 'absolute',
  top: '-12px',
  left: '-12px',
  opacity: 0,
  background: theme.palette.background.paper,
  ...(theme.palette.mode === 'dark' && {
    color: '#fff'
  }),
  fontSize: theme.mixins.pxToRem(12),
  width: '24px',
  height: '24px',
  '&:hover': {
    opacity: '0.7 !important',
    background: theme.palette.background.paper
  }
}));

interface Props {
  item: TextItemProps;
  setListText?: any;
  widthContainer?: number;
  heightContainer?: number;
  ratio?: number;
  onSelectItemText?: (item: TextItemProps) => void;
}

function TextItemDraggable({
  item,
  setListText,
  widthContainer,
  heightContainer,
  ratio,
  onSelectItemText
}: Props) {
  const { i18n } = useGlobal();
  const clickTextRef = React.useRef<boolean>();
  const textRef = React.useRef<HTMLDivElement>();

  const [disabledDrag, setDisabledDrag] = React.useState<boolean>(false);

  const enableDrag = React.useCallback(() => setDisabledDrag(false), []);

  const disableDrag = React.useCallback(() => setDisabledDrag(true), []);

  const boundTextDrag = React.useMemo(() => {
    const element = textRef.current?.getBoundingClientRect();
    const widthEle = element?.width;
    const heightEle = element?.height;

    if (!widthEle && !heightEle) return 'parent';

    return {
      top: 0,
      left: 0,
      right: widthContainer - (widthEle + 2 || 0),
      bottom: heightContainer - (heightEle + 2 || 0)
    };
  }, [widthContainer, heightContainer, textRef]);

  const handleDeleteText = data => {
    setListText(prev => prev.filter(item => item.id !== data.id));
  };

  const onStopDraggable = (_, { x, y }, dataItem) => {
    if (!dataItem) return;

    setListText(prev =>
      prev.map(item => {
        if (item?.id === dataItem?.id) {
          return { ...item, position: { x, y } };
        }

        return item;
      })
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  };

  const handleClickText = data => {
    onSelectItemText(data);
  };

  return (
    <Draggable
      ref={textRef}
      disabled={disabledDrag}
      bounds={boundTextDrag}
      key={item.id}
      position={item?.position}
      onStop={(e: Event, data: DraggableData) => {
        onStopDraggable(e, data, item);

        if (!clickTextRef.current) return;

        handleClickText(item);
      }}
      onDrag={() => {
        if (!clickTextRef.current) return;

        clickTextRef.current = false;
      }}
      onMouseDown={() => {
        clickTextRef.current = true;
      }}
    >
      <TextContent
        fontFamily={item?.fontFamily}
        style={{
          color: item?.color ?? DEFAULT_COLOR
        }}
        fontSize={(item?.fontSize ?? DEFAULT_FONTSIZE) * ratio}
      >
        <Box sx={{ whiteSpace: 'pre-line' }}>
          {item?.text || i18n.formatMessage({ id: 'start_typing' })}
        </Box>
        <IconButtonStyled
          onMouseLeave={enableDrag}
          onMouseEnter={disableDrag}
          onClick={() => {
            handleDeleteText(item);
            enableDrag();
          }}
        >
          <LineIcon icon="ico-close" />
        </IconButtonStyled>
      </TextContent>
    </Draggable>
  );
}

export default TextItemDraggable;
