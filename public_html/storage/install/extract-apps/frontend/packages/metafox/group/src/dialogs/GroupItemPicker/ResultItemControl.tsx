/**
 * @type: ui
 * name: group.ui.pickGroupItem
 */
import { PickerItemControlProps } from '@metafox/framework';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar,
  ItemSummary
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

function ResultItemControl(props: PickerItemControlProps) {
  const { item, onClick, wrapAs, wrapProps } = props;

  const itemAvatar = {
    full_name: item.title,
    avatar: item?.cover
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      onClick={onClick}
      testid={`${item.resource_name}`}
    >
      <ItemMedia>
        <UserAvatar user={itemAvatar} size={40} variant="rounded" />
      </ItemMedia>
      <ItemText>
        <ItemTitle>{item.title}</ItemTitle>
        {item.reg_name ? <ItemSummary>{item.reg_name}</ItemSummary> : null}
      </ItemText>
    </ItemView>
  );
}

ResultItemControl.LoadingSkeleton = props => {
  return (
    <ItemView {...props}>
      <ItemMedia>
        <Skeleton variant="avatar" width={40} height={40} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="50%" />
          <Skeleton variant="text" width="30%" />
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
};

export default ResultItemControl;
