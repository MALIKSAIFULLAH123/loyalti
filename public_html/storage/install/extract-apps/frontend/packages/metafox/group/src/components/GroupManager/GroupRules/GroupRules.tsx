/**
 * @type: itemView
 * name: groupRule.itemView.mainCard
 * chunkName: group
 */

import { connectItemView, connectSubject, useGlobal } from '@metafox/framework';
import { ItemText, ItemTitle, ItemView, ItemAction } from '@metafox/ui';
import React from 'react';
import { GroupRuleProps } from '../MembershipQuestion/types';
import { DropTargetMonitor, useDrag, useDrop } from 'react-dnd';
import HtmlViewer from '@metafox/html-viewer';

const ItemTypes = {
  ROW: 'row'
};

const GroupRuleCard = ({
  item,
  identity,
  wrapAs,
  wrapProps,
  state,
  handleAction,
  index
}: GroupRuleProps) => {
  const { ItemActionMenu, dispatch } = useGlobal();
  const itemRef = React.useRef<HTMLDivElement>(null);
  const [, drop] = useDrop({
    accept: ItemTypes.ROW,
    drop(item, monitor: DropTargetMonitor) {
      if (!itemRef.current) {
        return;
      }

      const dragIndex = item.index;
      const hoverIndex = index;

      if (dragIndex === hoverIndex) {
        return;
      }

      dispatch({
        type: 'group/orderingRule',
        payload: {
          dragIndex,
          hoverIndex,
          identity
        }
      });
    }
  });

  const [isDragging, drag] = useDrag({
    type: ItemTypes.ROW,
    item: { index },
    collect: (monitor: any) => ({
      isDragging: monitor.isDragging()
    })
  });

  const opacity = isDragging.isDragging ? 0.5 : 1;

  drag(drop(itemRef));

  const styleDnd: React.CSSProperties = {
    cursor: 'move'
  };

  if (!item) return null;

  const { title, description } = item;

  return (
    <ItemView
      testid={'GroupRuleCard'}
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      ref={itemRef}
      style={{ ...styleDnd, opacity }}
    >
      <ItemText>
        <ItemTitle marginBottom={2} marginRight={3}>
          {title}
        </ItemTitle>
        <ItemAction placement="top-end">
          <ItemActionMenu
            identity={identity}
            icon={'ico-dottedmore-vertical-o'}
            state={state}
            handleAction={handleAction}
          />
        </ItemAction>
        <HtmlViewer html={description || ''} />
      </ItemText>
    </ItemView>
  );
};

ItemView.displayName = 'GroupRuleItem_MainCard';

export default connectSubject(connectItemView(GroupRuleCard, () => {}));
