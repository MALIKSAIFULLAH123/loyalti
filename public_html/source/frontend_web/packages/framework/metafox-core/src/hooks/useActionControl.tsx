/**
 * @type: service
 * name: useActionControl
 */
import { HandleAction, useGlobal } from '@metafox/framework';
import { assign, isString } from 'lodash';
import React, { useEffect, useRef } from 'react';

export default function useActionControl<T = unknown, U = unknown>(
  identity: string,
  initial: T,
  actionCreators?: (
    handleAction: HandleAction,
    dispatch: (type: string, payload: unknown) => void
  ) => U
): [HandleAction, T, (prev: T) => T | void, U] {
  const { dispatch } = useGlobal();
  const mounted = useRef<boolean>(true);
  const [localState, setState] = React.useState<T>(initial);

  useEffect(() => {
    mounted.current = true;

    return () => {
      mounted.current = false;
    };
  }, []);

  const setLocalState = React.useCallback((cb: any) => {
    if (mounted.current) {
      setState(cb);
    }
  }, []);

  const handleAction = React.useCallback(
    (types: string, payload?: unknown, meta?: unknown) => {
      if (!isString(types)) return;

      types
        .split(',')
        .map(x => x.trim())
        .filter(Boolean)
        .forEach(type => {
          dispatch({
            type,
            payload: assign({ identity }, payload),
            meta: assign({ setLocalState }, meta)
          });
        });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [identity]
  );

  const actions = React.useMemo(() => {
    return actionCreators ? actionCreators(handleAction, dispatch) : undefined;
  }, [actionCreators, dispatch, handleAction]);

  return [handleAction, localState, setLocalState, actions];
}
