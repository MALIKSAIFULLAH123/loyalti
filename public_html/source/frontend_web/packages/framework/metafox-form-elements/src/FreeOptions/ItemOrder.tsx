import { Box } from '@mui/material';
import React from 'react';
import { useDrag, useDrop } from 'react-dnd';
import type { Identifier, XYCoord } from 'dnd-core';
import { styled } from '@mui/material/styles';

interface DragItem {
  index: number;
  type: string;
}

interface RowProps {
  children: any;
  index: number;
  onMoveRow: (dragIndex: number, hoverIndex: number) => void;
  handleDrop: () => void;
  sortable?: boolean;
  disabled?: boolean;
}

const ItemTypes = {
  ROW: 'row'
};

const name = 'FreeOptionsSortItem';

const FieldStyled = styled(Box, {
  name,
  slot: 'FieldSortable',
  shouldForwardProp: prop => prop !== 'sortable'
})<{ sortable: boolean }>(({ theme, sortable }) => ({
  width: '100%',
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(1),
  '&:not(:first-of-type)': {
    marginTop: theme.spacing(1)
  },
  ...(sortable && {
    '&:hover': {
      cursor: 'move'
    }
  })
}));

function ItemField({
  children,
  index,
  onMoveRow,
  handleDrop,
  sortable = false,
  disabled = false
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

      onMoveRow(dragIndex, hoverIndex);

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
      return { index };
    },
    collect: (monitor: any) => ({
      isDragging: monitor.isDragging()
    }),
    canDrag: !disabled,
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
        sortable
        ref={ref}
        data-handler-id={handlerId}
        style={{ opacity }}
      >
        {children}
      </FieldStyled>
    );
  }

  return <FieldStyled>{children}</FieldStyled>;
}

export default ItemField;
