/**
 * @type: ui
 * name: page_member.ui.pickItem
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar } from '@mui/material';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

type ResultItemControlProps = {
  item: any;
  classes: Record<string, string>;
  onClick?: () => void;
  wrapAs: any;
  wrapProps: any;
};

function ResultItemControl(props: ResultItemControlProps) {
  const { item, onClick, wrapAs, wrapProps } = props;
  const user = item?.user;

  return (
    <ItemView onClick={onClick} wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Avatar
          sx={{ width: 40, height: 40 }}
          src={getImageSrc(user?.avatar, '240')}
        >
          {shortenFullName(user?.full_name)}
        </Avatar>
      </ItemMedia>
      <ItemText>
        <ItemTitle>{user?.full_name}</ItemTitle>
      </ItemText>
    </ItemView>
  );
}

ResultItemControl.LoadingSkeleton = LoadingSkeleton;

export default ResultItemControl;
