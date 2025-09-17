import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { PhotoDetailProps } from '@metafox/photo/types';
import React from 'react';

export type Props = PhotoDetailProps;

export default function PhotoDetail(props: Props) {
  const { item, identity } = props || {};
  const { jsxBackend, usePageParams } = useGlobal();
  const { photo_set, photo_album, photo_id, media_type } = usePageParams();
  const MediaViewContainer = jsxBackend.get('media.ui.viewBlockMobile');

  if (!item) return null;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <MediaViewContainer
          {...props}
          photo_set={photo_set}
          photo_album={photo_album}
          identity={identity}
          photo_id={photo_id}
          media_type={media_type}
        />
      </BlockContent>
    </Block>
  );
}
