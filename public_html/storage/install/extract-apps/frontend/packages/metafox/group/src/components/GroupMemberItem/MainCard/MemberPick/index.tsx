/**
 * @type: ui
 * name: group_member.ui.pickItem
 * chunkName: group
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { getImageSrc, shortenFullName, colorHash } from '@metafox/utils';
import { Avatar } from '@mui/material';
import React from 'react';

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
          src={getImageSrc(user.avatar, '240')}
          children={shortenFullName(user.full_name)}
          style={{
            backgroundColor: colorHash.hex(
              shortenFullName(user.full_name) || ''
            )
          }}
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle>{user.full_name}</ItemTitle>
      </ItemText>
    </ItemView>
  );
}

export default ResultItemControl;
