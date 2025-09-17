import { get, set } from 'lodash';
import dotProps from './inspectDotProps';

type MixValues = Record<string, any>;

export default function mergeObjectProps<T = MixValues>(
  baseProps: MixValues,
  baseDefaults: MixValues,
  defaults: MixValues,
  values: MixValues,
  skips: MixValues,
  overrides: MixValues = {}
): T {
  const mixValues: MixValues = {};
  const keyProps: Record<string, boolean> = {};

  if (baseProps) dotProps(baseProps, true, keyProps);

  if (baseDefaults) dotProps(values, true, keyProps);

  if (values) dotProps(values, true, keyProps);

  if (defaults) dotProps(defaults, true, keyProps);

  if (skips) dotProps(skips, false, keyProps);

  if (overrides) dotProps(overrides, true, keyProps);

  Object.keys(keyProps)
    .filter(key => keyProps[key])
    .forEach(key => {
      let value;

      if (overrides) value = get(overrides, key);

      if (value === undefined) value = get(values, key);

      if (value === undefined) value = get(defaults, key);

      if (value === undefined) value = get(baseDefaults, key);

      if (value === undefined) value = get(baseProps, key);

      if (value !== undefined) set(mixValues, key, value);
    });

  return mixValues as T;
}
