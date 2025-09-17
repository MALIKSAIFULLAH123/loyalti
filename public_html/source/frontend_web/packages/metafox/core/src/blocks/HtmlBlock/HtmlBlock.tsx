/**
 * @type: block
 * name: core.block.html
 * title: Html Block
 * keywords: general
 * description: Custom Html
 */

import { createBlock } from '@metafox/framework';
import React from 'react';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import HtmlViewer from '@metafox/html-viewer';
import scriptTransform from './scriptTransform';
import transformDangerous from '@metafox/html-viewer/transformDangerous';

function HtmlBlock({ title, content, disableNl2br, script }) {
  const [init, setInit] = React.useState(false);

  React.useEffect(() => {
    setInit(true);
  }, []);

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <HtmlViewer
          html={content}
          disableNl2br={disableNl2br}
          transform={transformDangerous}
        />
        {init && script ? (
          <HtmlViewer html={script} transform={scriptTransform} disableNl2br />
        ) : null}
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: HtmlBlock,
  defaults: {
    title: 'Custom Html'
  },
  custom: {
    content: {
      component: 'Textarea',
      name: 'content',
      variant: 'outlined',
      label: 'Html'
    },
    script: {
      component: 'Textarea',
      name: 'script',
      variant: 'outlined',
      label: 'Script'
    }
  }
});
