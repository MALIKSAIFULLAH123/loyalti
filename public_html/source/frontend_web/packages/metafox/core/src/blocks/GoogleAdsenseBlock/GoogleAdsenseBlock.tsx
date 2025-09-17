/**
 * @type: block
 * name: core.block.googleAdsense
 * title: Google Adsense Block
 * keywords: general, google adsense
 * description: Google Adsense Block
 */

import { createBlock } from '@metafox/framework';
import React from 'react';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import HtmlViewer from '@metafox/html-viewer';

function GoogleAdsenseBlock({
  title,
  content,
  disableNl2br,
  google_ad_client
}) {
  React.useEffect(() => {
    try {
      setTimeout(() => {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
      }, 500);
    } catch (error) {}
  }, []);

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <HtmlViewer html={content} disableNl2br={disableNl2br} />
      </BlockContent>
    </Block>
  );
}

export default createBlock({
  extendBlock: GoogleAdsenseBlock,
  defaults: {
    title: 'Google Adsense'
  },
  custom: {
    content: {
      component: 'Textarea',
      name: 'content',
      variant: 'outlined',
      label: 'Ins element'
    }
  }
});
