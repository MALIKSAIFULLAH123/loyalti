/**
 * @type: service
 * name: DialogContainer
 */
import { useGlobal } from '@metafox/framework';
import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { ModalItemParams } from './types';
import { detect } from 'detect-browser';

let top = 0;
let shouldUpdateScrollOffset = false;

export default function DialogContainer(): JSX.Element {
  const { jsxBackend, dialogBackend, navigate } = useGlobal();
  const [items, setItems] = React.useState<ModalItemParams[]>([]);
  const location = useLocation();
  const { pathname: _pathname, key, state, search } = location || {};
  const pathname = state?.as || _pathname;
  const browser = detect();
  // ios lost position when open dialog and virtual keyboard
  const { name } = browser || {};
  const iOS = ['ios', 'crios'].includes(name);
  const IS_APPLE_MOBILE =
    iOS ||
    /iPhone|iPad/.test(navigator.platform) ||
    (navigator.userAgent.includes('Mac') && 'ontouchend' in document);

  // use ref to prevent re render
  const lastCounter = React.useRef<number>(0);
  const lastState = React.useRef<{
    pathname: string;
    state: any;
    search: string;
  }>({
    pathname,
    state,
    search
  });

  React.useEffect(() => {
    dialogBackend.subscribe(setItems);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (!state?.asModal && dialogBackend) {
      dialogBackend.dismiss(true);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname]);

  React.useEffect(() => {
    if (window?.document && IS_APPLE_MOBILE) {
      const body = window.document.body;

      const observer = new MutationObserver(() => {
        let bodyPosition = '';

        if (body.style.overflow === 'hidden') {
          const yOffset = window.pageYOffset || window.scrollY;
          top = yOffset > 0 && top === 0 ? yOffset : top;
          bodyPosition = window.document.body.style.position;

          shouldUpdateScrollOffset = true;
          window.document.body.style.top = `-${top}px`;
          window.document.body.style.position = 'fixed';
          window.document.body.style.left = '0';
          window.document.body.style.right = '0';
        } else {
          if (shouldUpdateScrollOffset) {
            window.document.body.style.position = bodyPosition;
            window.document.body.style.left = '';
            window.document.body.style.right = '';
            window.document.body.style.top = '';
            window.scrollTo(0, top);
            top = 0;
            shouldUpdateScrollOffset = false;
          }
        }
      });

      observer.observe(body, {
        attributes: true,
        attributeFilter: ['style']
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    const counter = items.filter(x => x.open).length;

    if (!state?.asModal) {
      lastState.current = { pathname, state, search };
    }

    // revert to last url when all popup/modal route is close.
    if (counter === 0 && lastCounter.current !== counter) {
      if (lastState.current.pathname !== pathname) {
        navigate(
          {
            pathname: lastState.current.pathname,
            search: lastState.current.search
          },
          {
            replace: true,
            state: Object.assign({}, lastState.current.state, {
              keepScroll: true
            })
          }
        );
      }
    }

    lastCounter.current = counter;

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pathname, key, items, search]);

  if (!items.length) return null;

  return jsxBackend.render(items);
}
