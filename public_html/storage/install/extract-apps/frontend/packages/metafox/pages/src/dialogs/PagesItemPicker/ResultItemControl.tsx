/**
 * @type: ui
 * name: page.ui.pickPageItem
 */
import { PickerItemControlProps } from '@metafox/framework';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  UserAvatar
} from '@metafox/ui';
import { Skeleton } from '@mui/material';
import React from 'react';

function ResultItemControl(props: PickerItemControlProps) {
  const { item, wrapAs, wrapProps, onClick } = props;

  const itemAvatar = {
    full_name: item.title,
    avatar: item?.avatar || item?.image
  };

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      onClick={onClick}
      data-testid="suggestion"
      role="option"
      testid={`${item.resource_name}`}
    >
      <ItemMedia>
        <UserAvatar user={itemAvatar} size={40} />
      </ItemMedia>
      <ItemText>
        <ItemTitle>{item.title}</ItemTitle>
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
        </ItemTitle>
      </ItemText>
    </ItemView>
  );
};

export default ResultItemControl;
