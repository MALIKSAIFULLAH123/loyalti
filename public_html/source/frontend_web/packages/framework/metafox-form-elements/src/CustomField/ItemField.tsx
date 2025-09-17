import { Box } from '@mui/material';
import React from 'react';
import { useDrag, useDrop } from 'react-dnd';
import type { Identifier, XYCoord } from 'dnd-core';
import { styled } from '@mui/material/styles';
import { LineIcon } from '@metafox/ui';
import { isEmpty } from 'lodash';
import ErrorMessage from '../ErrorMessage';

interface DragItem {
  index: number;
  id: string;
  type: string;
}

interface RowProps {
  id: any;
  children: any;
  index: number;
  moveRow: (dragIndex: number, hoverIndex: number) => void;
  handleDrop: () => void;
  sortable?: boolean;
  error?: any;
  disable?: boolean;
}

const ItemTypes = {
  ROW: 'row'
};

const name = 'FieldStyled';

const FieldStyled = styled(Box, {
  name,
  slot: 'FieldSortable',
  shouldForwardProp: props => props !== 'error'
})<{ error?: any }>(({ theme, error }) => ({
  width: '100%',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1),
  '&:hover': {
    cursor: 'move',
    backgroundColor: theme.palette.action.selected,
    borderRadius: theme.shape.borderRadius
  },
  ...(error && {
    flexDirection: 'column'
  })
}));

const WrapperIcon = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  width: 32,
  minWidth: 32,
  padding: theme.spacing(1.25, 0.5),
  textAlign: 'left',
  justifyContent: 'left',
  overflow: 'hidden'
}));

function ItemField({
  id,
  children,
  index,
  moveRow,
  handleDrop,
  sortable = false,
  error,
  disable = false
}: RowProps) {
  const ref = React.useRef<HTMLDivElement>(null);
  const [{ handlerId }, drop] = useDrop<
    DragItem,
    void,
    { handlerId: Identifier | null }
  >({
    accept: ItemTypes.ROW,
    collect(monitor) {
      return {
        handlerId: monitor.getHandlerId()
      };
    },
    drop() {
      handleDrop();
    },
    hover(item: DragItem, monitor) {
      if (!ref.current) {
        return;
      }

      const dragIndex = item.index;
      const hoverIndex = index;

      // Don't replace items with themselves
      if (dragIndex === hoverIndex) {
        return;
      }

      // Determine rectangle on screen
      const hoverBoundingRect = ref.current?.getBoundingClientRect();

      // Get vertical middle
      const hoverMiddleY =
        (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;

      // Determine mouse position
      const clientOffset = monitor.getClientOffset();

      // Get pixels to the top
      const hoverClientY = (clientOffset as XYCoord).y - hoverBoundingRect.top;

      // Only perform the move when the mouse has crossed half of the items height
      // When dragging downwards, only move when the cursor is below 50%
      // When dragging upwards, only move when the cursor is above 50%

      // Dragging downwards
      if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) {
        return;
      }

      // Dragging upwards
      if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) {
        return;
      }

      moveRow(dragIndex, hoverIndex);

      // Note: we're mutating the monitor item here!
      // Generally it's better to avoid mutations,
      // but it's good here for the sake of performance
      // to avoid expensive index searches.
      item.index = hoverIndex;
    }
  });

  const [{ isDragging }, drag] = useDrag({
    type: ItemTypes.ROW,
    item: () => {
      return { id, index };
    },
    collect: (monitor: any) => ({
      isDragging: monitor.isDragging()
    }),
    canDrag: !disable,
    end: (item, monitor) => {
      const didDrop = monitor.didDrop();

      if (!didDrop) {
        // drop outside target
        handleDrop();
      }
    }
  });

  const opacity = isDragging ? 0 : 1;
  drag(drop(ref));

  if (sortable) {
    return (
      <FieldStyled
        pt={2}
        ref={ref}
        data-handler-id={handlerId}
        style={{ opacity }}
        error={error}
      >
        <Box sx={{ display: 'flex', width: '100%' }}>
          <WrapperIcon>
            <LineIcon icon={'ico-arrows-move'} />
          </WrapperIcon>
          {children}
        </Box>
        {!isEmpty(error) && (
          <Box pl={4} width={'100%'}>
            <ErrorMessage error={error} />
          </Box>
        )}
      </FieldStyled>
    );
  }

  return <Box pt={2}>{children}</Box>;
}

export default ItemField;
