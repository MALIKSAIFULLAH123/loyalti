import { isArray, isPlainObject } from 'lodash';

export default function inspectDotProps(
  values: Record<string, any>,
  flag: boolean,
  keyProps: Record<string, boolean>,
  parent?: string
) {
  if (isPlainObject(values)) {
    Object.keys(values).forEach(key => {
      const name = parent ? `${parent}.${key}` : key;

      if (isArray(values[key])) {
        keyProps[name] = flag;
      } else if (isPlainObject(values[key])) {
        inspectDotProps(values[key], flag, keyProps, name);
      } else {
        keyProps[name] = flag;
      }
    });
  }
}
