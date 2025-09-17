/**
 * @type: ui
 * name: layout.slot.EditLayout
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Button, ButtonGroup, Grid, Tooltip } from '@mui/material';
import { styled } from '@mui/material/styles';
import { camelCase } from 'lodash';
import React from 'react';
import { useDrop } from 'react-dnd';
import { TYPE_BLOCK } from '../constants';
import { LayoutSlotProps, PageSize } from '../types';
import useLayout from '../useLayout';
import SlotHeader from './SlotHeader';
import { Slot, SlotContent, SlotStage } from './StyledSlot';
interface DragBlockControllerType {
  blockId: string;
}

interface ToolbarProps {
  slotName: string;
  pageName: string;
  templateName: string;
  pageSize: PageSize;
  elementPath: string;
}

const ToolbarRoot = styled('div')(({ theme }) => ({
  textAlign: 'right',
  padding: theme.spacing(1),
  '&:hover': {
    opacity: 1
  }
}));

const Toolbar = (props: ToolbarProps) => {
  const { dispatch, i18n } = useGlobal();
  const handle = (type: string) =>
    dispatch({
      type,
      payload: props
    });

  return (
    <ToolbarRoot>
      <ButtonGroup size="small" color="primary">
        <Button onClick={() => handle('@layout/editSlot')}>
          <Tooltip
            enterDelay={0}
            title={i18n.formatMessage({ id: 'edit_layout_slot' })}
          >
            <LineIcon icon="ico-pencilline-o" />
          </Tooltip>
        </Button>
        <Button onClick={() => handle('@layout/deleteSlot')}>
          <Tooltip title={i18n.formatMessage({ id: 'delete_layout_slot' })}>
            <LineIcon icon="ico-trash-o" />
          </Tooltip>
        </Button>
      </ButtonGroup>
    </ToolbarRoot>
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
export default function SlotWithEditingLayout({
  elementPath,
  templateName,
  slotName,
  flexWeight,
  xs,
  rootStyle,
  stageStyle,
  contentStyle,
  elements
}: LayoutSlotProps) {
  const slotRef = React.useRef<HTMLDivElement>();
  const { pageSize, pageName, blocks } = useLayout();
  const { jsxBackend, layoutBackend } = useGlobal();

  const items = blocks.filter(item => item.slotName === slotName);

  // eslint-disable-next-line

  const itemCount = items.length;
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
    }
  });

  const useFlex = '0' < flexWeight || !xs;

  return (
    <Slot
      name={slotName}
      item
      useFlex={useFlex}
      flexWeight={flexWeight}
      xs={useFlex ? undefined : xs}
      ref={drop}
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
        <SlotContent name={slotName} ref={slotRef} {...contentStyle}>
          {elements?.length ? (
            <Grid container>{jsxBackend.render(elements)}</Grid>
          ) : null}
          <Box>{jsxBackend.render(items)}</Box>
        </SlotContent>
      </SlotStage>
    </Slot>
  );
}
