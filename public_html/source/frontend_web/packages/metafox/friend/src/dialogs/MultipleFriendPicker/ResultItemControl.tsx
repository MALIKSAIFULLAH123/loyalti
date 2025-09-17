/**
 * @type: ui
 * name: friend.ui.pickItem
 */
import { ItemMedia, ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { colorHash, getImageSrc, shortenFullName } from '@metafox/utils';
import { Avatar } from '@mui/material';
import React from 'react';

type ResultItemControlProps = {
  item: any;
  onClick?: () => void;
  wrapAs: any;
  wrapProps: any;
};

function ResultItemControl(props: ResultItemControlProps) {
  const { item, onClick, wrapAs, wrapProps } = props;

  return (
    <ItemView onClick={onClick} wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemMedia>
        <Avatar
          sx={{ width: 40, height: 40 }}
          src={getImageSrc(item?.avatar, '200')}
          children={shortenFullName(item.full_name)}
          style={{
            backgroundColor: colorHash.hex(
              shortenFullName(item.full_name) || ''
            )
          }}
        />
      </ItemMedia>
      <ItemText>
        <ItemTitle>{item.full_name}</ItemTitle>
      </ItemText>
    </ItemView>
  );
}

export default ResultItemControl;
