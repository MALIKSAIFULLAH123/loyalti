/**
 * @type: block
 * name: layout.Fragment
 * title: React.Fragment
 * keywords: general
 * experiment: true
 */
import { useGlobal } from '@metafox/framework';
import { isArray } from 'lodash';

export default function Fragment({ elements }) {
  const { jsxBackend } = useGlobal();

  if (!isArray(elements) || !elements.length) {
    return null;
  }

  return jsxBackend.render(elements);
}
