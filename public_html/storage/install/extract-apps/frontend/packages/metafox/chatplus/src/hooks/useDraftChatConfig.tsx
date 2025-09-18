import { useGlobal } from '@metafox/framework';
import { filterShowWhen, withDisabledWhen } from '@metafox/utils';
import { useMemo, useRef } from 'react';

export default function useDraftEditorConfig(
  config: { editorPlugins: any; editorControls: any; attachers?: any },
  condition?: Record<string, any>,
  rid?: string,
  room?: any,
  isAllPage?: boolean
) {
  const { jsxBackend } = useGlobal();
  const editorPlugins = useRef([]);
  const editorComponents = useRef([]);
  const deps = isAllPage ? [rid, room?.id, condition] : [condition];

  return useMemo(() => {
    if (room && rid)
      config.editorPlugins.forEach(plugin => {
        const creator = jsxBackend.get(plugin.as);

        if (creator && 'function' === typeof creator) {
          creator(editorPlugins.current, editorComponents.current, rid, room);
        }
      });

    const editorControls = config.editorControls
      ? withDisabledWhen(
          filterShowWhen(config.editorControls as any, condition),
          condition
        )
      : [];

    const editorAttachers = config.attachers
      ? withDisabledWhen(filterShowWhen(config.attachers, condition), condition)
      : [];

    return [
      editorPlugins.current,
      editorComponents.current,
      editorControls,
      editorAttachers
    ];
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, deps);
}
