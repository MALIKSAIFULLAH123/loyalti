import { keyframes } from '@emotion/react';
import { isEmpty } from 'lodash';

export function getClosestElement(elementProp: HTMLElement | null): {
  element: HTMLElement | null;
} {
  let current = elementProp;

  while (current && current !== document.body) {
    if (current.hasAttribute('data-testid')) {
      return { element: current };
    }

    current = current.parentElement;
  }

  if (document.body.hasAttribute('data-testid')) {
    return { element: document.body };
  }

  return { element: null };
}

export function getClosestPath(element): string {
  const path = ['body'];
  let current = element;

  while (current && current !== document.body) {
    if (current.hasAttribute('data-testid')) {
      const testId = current.getAttribute('data-testid');
      path.splice(1, 0, `[data-testid="${testId}"]`);
    }

    current = current.parentElement;
  }

  return path.join(' ');
}

export const drawElementSelected = elementPath => {
  try {
    const element = document.querySelector(elementPath);

    if (!elementPath || !element) return;

    element.classList.add('tourguide-selected');
  } catch (err) {}
};

export const removeStyleElementSelected = elementPath => {
  try {
    const element = document.querySelector(elementPath);

    if (!elementPath || !element) return;

    element.classList.remove('tourguide-selected');
  } catch (err) {}
};

export function findElementAndRemoveClass(className: string) {
  try {
    const elements = document.getElementsByClassName(className) || [];

    if (isEmpty(elements)) return;

    Array.from(elements)?.forEach(element => {
      element.classList.remove(className);
    });
  } catch (err) {}
}

const viewPortHeight =
  window.innerHeight || document.documentElement.clientHeight;

const viewPortWidth = window.innerWidth || document.documentElement.clientWidth;
const thresholdHeader = 80;

export function isElementInViewport(el) {
  if (!el) return false;

  const rect = el.getBoundingClientRect();

  return (
    rect.top >= thresholdHeader &&
    rect.left >= 0 &&
    rect.bottom <= viewPortHeight &&
    rect.right <= viewPortWidth
  );
}

export function scrollToElementIfNeeded(element: HTMLElement) {
  if (!element) return;

  if (!isElementInViewport(element)) {
    if (isElementFixed(element)) {
      element.scrollIntoView({
        behavior: 'auto',
        block: 'start',
        inline: 'nearest'
      });
    } else {
      const scrollY = window.pageYOffset || window.scrollY || 0;
      const yOffset = window.innerHeight / 4;
      const top = element.getBoundingClientRect().top + scrollY - yOffset;

      window.scrollTo({ top, behavior: 'auto' });
    }
  }
}

export function isBoundSmallerView(
  el,
  thresholdHeight = 0,
  thresholdWidth = 0
) {
  if (!el) return false;

  const rect = el.getBoundingClientRect();

  return (
    rect.height + thresholdHeight <
      (window.innerHeight || document.documentElement.clientHeight) &&
    rect.width + thresholdWidth <
      (window.innerWidth || document.documentElement.clientWidth)
  );
}

export const fadeInLeftAnimation = keyframes`
    0% {
      opacity: 0;
      WebkitTransform: translate3d(-100%, 0, 0);
      transform: translate3d(-100%, 0, 0);
    }
    100% {
      opacity: 1;
      WebkitTransform: none;
      transform: none;
      }
    }
`;

export const fadeInRightAnimation = keyframes`
    0% {
      opacity: 0;
      WebkitTransform: translate3d(100%, 0, 0);
      transform: translate3d(100%, 0, 0);
    }
    100% {
      opacity: 1;
      WebkitTransform: none;
      transform: none;
      }
    }
`;

export function isElementFixed(element) {
  let current = element;

  while (current && current !== document.body) {
    const style = window.getComputedStyle(current);

    if (style.position === 'fixed' || style.position === 'sticky') {
      return true;
    }

    current = current.parentElement;
  }

  return false;
}
