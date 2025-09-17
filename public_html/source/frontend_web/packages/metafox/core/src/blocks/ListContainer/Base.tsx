import { useGlobal } from '@metafox/framework';
import { UIBlockViewProps } from '@metafox/ui';
import { isArray } from 'lodash';

export default function ListContainer({ elements }: UIBlockViewProps) {
  const { jsxBackend } = useGlobal();

  if (!isArray(elements) || !elements.length) return null;

  return jsxBackend.render(elements);
}
