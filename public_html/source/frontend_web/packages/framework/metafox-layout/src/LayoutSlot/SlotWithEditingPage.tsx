/**
 * @type: ui
 * name: layout.slot.EditPageContent
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  ButtonGroup,
  Grid,
  IconButton,
  styled,
  Tooltip
} from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import { useDrop } from 'react-dnd';
import { TYPE_BLOCK } from '../constants';
import { LayoutSlotProps, PageSize } from '../types';
import useLayout from '../useLayout';
import SlotHeader from './SlotHeader';
import { Slot, SlotStage } from './StyledSlot';

interface DragBlockControllerType {
  slotName: string;
  blockName: any;
  blockId: string;
}

interface ToolbarProps {
  slotName: string;
  pageName: string;
  templateName: string;
  pageSize: PageSize;
  elementPath: string;
}

const StyledBox = styled('div')({
  textAlign: 'right',
  padding: 8,
  '&:hover': {
    opacity: 1
  }
});

const Toolbar = (props: ToolbarProps) => {
  const { slotName, pageName, elementPath, pageSize, templateName } = props;
  const { dispatch, i18n } = useGlobal();

  const addBlock = () =>
    dispatch({
      type: '@layout/createBlock',
      payload: {
        templateName,
        pageName,
        pageSize,
        elementPath,
        slotName
      }
    });

  return (
    <StyledBox>
      <ButtonGroup size="small" color="primary">
        <Tooltip title={i18n.formatMessage({ id: 'create_layout_block' })}>
          <span>
            <IconButton onClick={addBlock}>
              <LineIcon icon="ico-plus" />
            </IconButton>
          </span>
        </Tooltip>
      </ButtonGroup>
    </StyledBox>
  );
};

function getBlockOrder(
  clientRect: DOMRect,
  offset: any,
  itemCount: number
): number {
  if (!offset) return 0;

  const dh = itemCount ? clientRect.height / itemCount : -1;

  return Math.floor((offset.y - clientRect.y) / dh);
}

// Support Drag & Drop
export default function SlotWithEditingPage(props: LayoutSlotProps) {
  const {
    elementPath,
    templateName,
    slotName,
    flexWeight,
    xs,
    rootStyle,
    stageStyle,
    elements
  } = props;
  const slotRef = React.useRef<HTMLDivElement>();
  const { pageSize, pageName, blocks } = useLayout();
  const { jsxBackend, layoutBackend } = useGlobal();
  const refOrder = React.useRef(0);

  const generateItems = React.useCallback(() => {
    return blocks
      .filter(item => item.slotName === slotName)
      .map(item => {
        item.props.slotName = slotName;

        return item;
      });
  }, [blocks, slotName]);

  // eslint-disable-next-line
  const [itemsState, setItemsState] = React.useState(generateItems());
  const itemCount = itemsState.length;
  const [, drop] = useDrop<DragBlockControllerType, any, any>({
    accept: TYPE_BLOCK,
    collect: monitor => ({
      isOver: monitor.isOver(),
      canDrop: monitor.canDrop()
    }),
    canDrop: () => true,

    drop: (item, monitor) => {
      if (monitor.didDrop()) return undefined;

      const order = getBlockOrder(
        slotRef.current.getBoundingClientRect(),
        monitor.getClientOffset(),
        itemCount
      );
      const result = {
        blockId: item.blockId,
        order,
        slotName,
        templateName,
        pageSize,
        pageName
      };

      layoutBackend.updateBlockPosition(result);

      return result;
    },
    hover(item, monitor) {
      const order = getBlockOrder(
        slotRef.current.getBoundingClientRect(),
        monitor.getClientOffset(),
        itemCount
      );

      if (order !== refOrder.current && -1 <= order) {
        refOrder.current = order;
        const itemsOrder = itemsState.map((item, index) => {
          return item;
        });
        setItemsState([...itemsOrder]);
      }
    }
  });

  React.useEffect(() => {
    setItemsState(generateItems());
  }, [blocks, generateItems]);

  const useFlex = '0' < flexWeight || !xs;

  return (
    <Slot
      name={slotName}
      item
      xs={useFlex ? undefined : xs}
      ref={drop}
      flexWeight={flexWeight}
      useFlex={useFlex}
      data-testid={camelCase(`LayoutSlot_${slotName}`)}
      {...rootStyle}
    >
      <SlotStage name={slotName} controller {...stageStyle}>
        <SlotHeader slotName={slotName}>
          <Toolbar
            elementPath={elementPath}
            pageName={pageName}
            templateName={templateName}
            slotName={slotName}
            pageSize={pageSize}
          />
        </SlotHeader>
        {elements?.length ? (
          <Grid container>{jsxBackend.render(elements)}</Grid>
        ) : null}
        <Box sx={{ minHeight: 60 }} ref={slotRef}>
          {jsxBackend.render(itemsState)}
        </Box>
      </SlotStage>
    </Slot>
  );
}
