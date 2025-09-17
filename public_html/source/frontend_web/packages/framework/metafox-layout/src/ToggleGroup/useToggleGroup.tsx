/**
 * @type: service
 * name: useToggleGroup
 */

import ToggleContext from './Context';
import { get } from 'lodash';
import { useContext } from 'react';

export default function useToggleGroup(
  group: string,
  name: string,
  initValue?: boolean
): [boolean, () => void] {
  const [value, setValue] = useContext(ToggleContext);

  const open = get(value, group, initValue) === name;

  const toggle = () => setValue({ ...value, [group]: open ? '' : name });

  return [open, toggle];
}
