import { NativeTypes } from 'react-dnd-html5-backend';
import { useDrop, DropTargetMonitor } from 'react-dnd';
import React from 'react';

export interface TargetBoxProps {
  onDrop: (item: { files: any[] }) => void;
}

export default function DropFileBox(props) {
  const { onDrop, render, ...rest } = props;
  const [{ canDrop, isOver }, drop] = useDrop(
    () => ({
      accept: [NativeTypes.FILE],
      drop(item: { files: any[] }) {
        if (onDrop) {
          onDrop(item.files);
        }
      },
      canDrop(item: any) {
        return true;
      },
      hover(item: any) {},
      collect: (monitor: DropTargetMonitor) => {
        return {
          isOver: monitor.isOver(),
          canDrop: monitor.canDrop()
        };
      }
    }),
    [props]
  );

  return (
    <div ref={drop} {...rest}>
      {render({ canDrop, isOver })}
    </div>
  );
}
