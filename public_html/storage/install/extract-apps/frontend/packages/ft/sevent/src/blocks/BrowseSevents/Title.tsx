
import { useGlobal } from '@metafox/framework';
import React from 'react';

export default function BrowseSeventsTitle() {
  const { usePageMeta } = useGlobal();
  const meta = usePageMeta();

  return (
    <h2 style={{ margin: 0, padding: 0 }}>{meta['og:title'] ? meta['og:title'] : ''}</h2>
  );
}