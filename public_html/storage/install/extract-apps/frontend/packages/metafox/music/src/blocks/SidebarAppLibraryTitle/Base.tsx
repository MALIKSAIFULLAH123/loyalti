import { Block, BlockHeader } from '@metafox/layout';
import React from 'react';

type Props = {
  title: string;
};

export default function PlaylistTitle({
  title
}: Props) {

  return (
    <Block>
      <BlockHeader title={title} />
    </Block>
  );
}
PlaylistTitle.displayName = 'PlaylistTitle';
