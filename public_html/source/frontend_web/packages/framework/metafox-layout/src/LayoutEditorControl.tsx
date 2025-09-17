/**
 * @type: ui
 * name: ui.controlCenterButton
 * chunkName: boot
 */
import loadable from '@loadable/component';
import {
  LAYOUT_EDITOR_TOGGLE as STORE_KEY,
  useGlobal
} from '@metafox/framework';
import React from 'react';

const LayoutEditor = loadable(
  () =>
    import(
      /* webpackChunkName: "layoutEditor" */
      '@metafox/layout/LayoutEditor'
    )
);

export type LayoutEditorControlProps = { pageName: string };

export default function LayoutEditorControl() {
  const { localStore, useIsMobile, constants, useSession, dispatch } =
    useGlobal();
  const isMobile = useIsMobile();
  const { user } = useSession();

  if (!user) {
    if (localStore.get(STORE_KEY)) {
      dispatch({ type: '@layout/closeEditor' });
    }

    return null;
  }

  // default is hidden
  if (!localStore.get(STORE_KEY) || isMobile) {
    if (!constants?.previewDevice) {
      return null;
    }
  }

  return <LayoutEditor />;
}
