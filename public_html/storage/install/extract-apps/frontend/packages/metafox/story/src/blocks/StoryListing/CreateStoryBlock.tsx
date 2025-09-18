import React from 'react';
import { Block, BlockContent } from '@metafox/layout';
import { useGlobal } from '@metafox/framework';

export default function CreateStoryBlock() {
  const { jsxBackend, getAcl } = useGlobal();
  const createAcl = getAcl('story.story.create');
  const CreateNewComponent = jsxBackend.get('story.ui.createStory');

  if (!createAcl || !CreateNewComponent) return null;

  return (
    <Block>
      <BlockContent>
        <CreateNewComponent />
      </BlockContent>
    </Block>
  );
}
