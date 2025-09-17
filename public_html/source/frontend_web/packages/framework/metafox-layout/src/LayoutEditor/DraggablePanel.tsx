/* eslint-disable eqeqeq */
import { useGlobal } from '@metafox/framework';
import { Box, Paper } from '@mui/material';
import { styled } from '@mui/material/styles';
import React, { useCallback, useRef } from 'react';
import loadable from '@loadable/component';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const name = 'LayoutEditor';
const POSITION = 'f4517565';
const PANEL_WIDTH: number = 240;

const Staged = styled(Box, { name, slot: 'Staged' })<{}>(({ theme }) => ({
  flip: false,
  zIndex: theme.zIndex.modal,
  display: 'flex',
  flexDirection: 'column',
  position: 'fixed',
  bottom: 'auto',
  right: 'auto'
}));

const Wrapper = styled(Paper, {
  name,
  slot: 'Content'
})(({ theme }) => ({
  width: PANEL_WIDTH,
  transition: theme.transitions.create('width'),
  overflow: 'hidden'
}));

const Header = styled('div', {
  name,
  slot: 'Header',
  shouldForwardProp: prop => prop != 'draggable'
})(() => ({
  display: 'flex',
  flexDirection: 'row',
  minHeight: 48,
  paddingRight: 8,
  alignItems: 'center',
  cursor: 'move',
  borderBottom: '1px solid rgba(0,0,0,0.15)'
}));

export default function DraggablePanel({ children, header }) {
  const { localStore } = useGlobal();

  const positionDefault = useRef(
    (localStore.get(POSITION) || '1%,10%').split(',')
  );

  const [draggable, setDraggable] = React.useState<boolean>(false);

  const contentRef = React.useRef<HTMLDivElement>();

  const enableDrag = React.useCallback(() => setDraggable(true), []);

  const disableDrag = React.useCallback(() => setDraggable(false), []);

  const onStopDraggable = useCallback(() => {
    const rect = contentRef.current.getBoundingClientRect();
    const x =
      rect.x + rect.width > window.innerWidth
        ? window.innerWidth - rect.width
        : Math.max(0, rect.x);
    const y =
      rect.y + rect.height > window.innerHeight
        ? window.innerHeight - rect.height
        : Math.max(0, rect.y);
    const left = `${(x / window.innerWidth) * 100}%`;
    const top = `${(y / window.innerHeight) * 100}%`;
    localStore.set(POSITION, `${left},${top}`);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Draggable onStop={onStopDraggable} disabled={!draggable}>
      <Staged
        dir="ltr"
        ref={contentRef}
        style={{
          left: positionDefault.current[0],
          top: positionDefault.current[1]
        }}
      >
        <Wrapper>
          <Header onMouseDownCapture={enableDrag} onMouseUp={disableDrag}>
            {header}
          </Header>
          {children}
        </Wrapper>
      </Staged>
    </Draggable>
  );
}
