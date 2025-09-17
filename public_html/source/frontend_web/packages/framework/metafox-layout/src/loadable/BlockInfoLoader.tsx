import loadable from '@loadable/component';
import { useGlobal } from '@metafox/framework';
import React from 'react';

const BlockInfo = loadable.lib(
  () =>
    import(
      /* webpackChunkName: "boot" */
      '@metafox/react-app/bundle-web/blockInfo'
    )
);

let loaded = false;

export default function BlockInfoLoader() {
  const { layoutBackend } = useGlobal();

  const setMeta = data => {
    loaded = true;
    layoutBackend.setBlockViews(data.default);
  };

  if (loaded) return null;

  return <BlockInfo ref={setMeta} />;
}
