/* eslint-disable react-hooks/rules-of-hooks */
import clsx from 'clsx';
import React from 'react';
import { DropTargetMonitor, useDrag, useDrop } from 'react-dnd';
import useStyles from './styles';

export const ItemTypes = {
  PHOTO: 'photo'
};

export default function ItemPhotoMove({
  id,
  index,
  item,
  movePhoto,
  allowDnD = false,
  children
}) {
  const classes = useStyles();
  const itemRef = React.useRef<HTMLDivElement>(null);

  if (!item) return;

  if (!allowDnD || !movePhoto) {
    return children;
  }

  const [, drop] = useDrop({
    accept: ItemTypes.PHOTO,
    drop(item, monitor: DropTargetMonitor) {
      if (!itemRef.current) {
        return;
      }

      const dragIndex = item.index;
      const hoverIndex = index;

      if (dragIndex === hoverIndex) {
        return;
      }

      movePhoto(dragIndex, hoverIndex);
      item.index = hoverIndex;
    }
  });

  const [isDragging, drag] = useDrag({
    type: ItemTypes.PHOTO,
    item: { type: ItemTypes.PHOTO, id, index },
    collect: (monitor: any) => ({
      isDragging: monitor.isDragging()
    })
  });

  const opacity = isDragging.isDragging ? 0.5 : 0.9999;
  const customStyle = isDragging.isDragging ? 'isDragging' : '';
  drag(drop(itemRef));

  const styleDnd: React.CSSProperties = {
    cursor: 'move'
  };

  return (
    <div
      data-uid={item.uid}
      style={{ ...styleDnd, opacity }}
      ref={itemRef}
      className={clsx(classes.itemRoot, customStyle ? classes.isDragging : '')}
    >
      {children}
    </div>
  );
}
