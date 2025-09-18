import { useGlobal } from '@metafox/framework';
import * as React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { VideoItemProps } from '@metafox/video/types';

function VideoViewMobile({
  identity,
  handleAction,
  state,
  ...rest
}: VideoItemProps) {
  const { jsxBackend, useGetItem } = useGlobal();
  const item = useGetItem(identity);

  if (!item) return null;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        {jsxBackend.render({
          component: 'video.ui.detailMobile',
          props: {
            identity
          }
        })}
      </BlockContent>
    </Block>
  );
}

export default VideoViewMobile;
