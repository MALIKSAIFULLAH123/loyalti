import React from 'react';
import loadable from '@loadable/component';
import { TextItemProps } from '@metafox/story/types';
import { DraggableData } from 'react-draggable';
import { useGlobal } from '@metafox/framework';
import { TextContent } from './Base';
import { DEFAULT_COLOR, DEFAULT_FONTSIZE } from '@metafox/story/constants';
import { Box } from '@mui/material';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

interface Props {
  item: TextItemProps;
  setListText?: any;
  widthContainer?: number;
  heightContainer?: number;
  onSelectItemText?: (item: TextItemProps) => void;
}

function TextItemDraggable({
  item,
  setListText,
  widthContainer,
  heightContainer,
  onSelectItemText
}: Props) {
  const { i18n } = useGlobal();
  const clickTextRef = React.useRef<boolean>();
  const textRef = React.useRef<HTMLDivElement>();

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

  const handleClickText = data => {
    onSelectItemText(data);
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

  return (
    <Draggable
      ref={textRef}
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
          color: item?.color ?? DEFAULT_COLOR,
          fontSize: item?.fontSize ?? DEFAULT_FONTSIZE
        }}
      >
        <Box sx={{ whiteSpace: 'pre-line' }}>
          {item?.text || i18n.formatMessage({ id: 'start_typing' })}
        </Box>
      </TextContent>
    </Draggable>
  );
}

export default TextItemDraggable;
