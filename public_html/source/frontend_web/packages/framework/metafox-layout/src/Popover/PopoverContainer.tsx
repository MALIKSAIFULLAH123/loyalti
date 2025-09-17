/**
 * @type: siteDock
 * name: core.popoverContainer
 */
import { useGlobal, HistoryState } from '@metafox/framework';
import { PopoverContext } from '@metafox/ui';
import { PopperPlacementType, PopperProps } from '@mui/material';
import * as React from 'react';
import { useLocation } from 'react-router-dom';

export interface PopoverContainerState {
  open: boolean;
  anchorEl?: PopperProps['anchorEl'];
  placement?: PopperPlacementType;
  content?: {
    props: Record<string, any>;
    component: string | React.ElementType;
  };
}

export default function PopoverContainer() {
  const { jsxBackend, popoverBackend } = useGlobal();
  const { pathname: _pathname, state } = useLocation<HistoryState>();
  const pathname = state?.as || _pathname;
  const [data, setData] = React.useState<PopoverContainerState>({
    open: false
  });

  const updateState = React.useCallback(
    (data: Partial<PopoverContainerState>) => {
      setData(prev => ({ ...prev, ...data }));
    },
    []
  );

  React.useEffect(() => {
    popoverBackend.subscribe(updateState);

    return () => {
      popoverBackend.unsubscribe();
    };
  }, [popoverBackend, updateState]);

  React.useEffect(() => {
    updateState({ open: false });
  }, [pathname, updateState]);

  const closePopover = React.useCallback(() => {
    setData(prev => ({
      ...prev,
      open: false
    }));
  }, []);
  const { anchorEl, open, content } = data;
  const opened = open && Boolean(anchorEl) && Boolean(content);

  if (!content?.component) return null;

  const component = jsxBackend.get(content.component);

  if (!component) return null;

  return (
    <PopoverContext.Provider value={{ closePopover }}>
      {jsxBackend.render({
        component: content.component,
        props: { ...content.props, open: opened, anchorEl }
      })}
    </PopoverContext.Provider>
  );
}
