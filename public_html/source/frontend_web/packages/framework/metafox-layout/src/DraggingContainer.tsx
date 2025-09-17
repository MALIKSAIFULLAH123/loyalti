/**
 * @type: service
 * name: DraggingContainer
 */
import { Box } from '@mui/material';
import React, { CSSProperties } from 'react';
import { useDragLayer } from 'react-dnd';

const layerStyles: CSSProperties = {
  position: 'fixed',
  pointerEvents: 'none',
  zIndex: 100,
  left: 0,
  top: 0
};

function getItemStyles(initialOffset, currentOffset) {
  if (!initialOffset || !currentOffset) {
    return {
      display: 'none'
    };
  }

  const { x, y } = currentOffset;
  const transform = `translate(${x}px, ${y}px)`;

  return {
    transform,
    WebkitTransform: transform
  };
}

export const DraggingContainer = () => {
  const { item, isDragging, initialOffset, currentOffset } =
    useDragLayer(monitor => ({
      item: monitor.getItem(),
      itemType: monitor.getItemType(),
      initialOffset: monitor.getInitialSourceClientOffset(),
      currentOffset: monitor.getSourceClientOffset(),
      isDragging: monitor.isDragging()
    }));

  if (!isDragging || !item?.draggingComponent) {
    return null;
  }

  const { draggingComponent: DraggingComponent } = item;

  return (
    <Box style={layerStyles}>
      <div style={getItemStyles(initialOffset, currentOffset)}>
        <DraggingComponent
          item={item}
          currentOffset={currentOffset}
          initialOffset={initialOffset}/>
      </div>
    </Box>
  );
};

export default DraggingContainer;
