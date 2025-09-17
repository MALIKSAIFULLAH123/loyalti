import { isArray, isObject, isPlainObject } from 'lodash';

function scanAllFileFields(
  values: any,
  dotProps: Record<string, true>,
  parent?: string
) {
  if (!isPlainObject(values) && !isArray(values)) {
    return;
  }

  Object.keys(values).forEach(key => {
    const name = parent ? `${parent}.${key}` : key;
    const val = values[key];

    // detect file not uploaded
    if (
      isPlainObject(val) &&
      !val.id &&
      val.status &&
      !val.temp_file && // old format need temp_file
      isObject(val.file) &&
      val.uid
    ) {
      if (
        val.status === 'create' ||
        val.status === 'new' ||
        val.status === 'update'
      ) {
        dotProps[name] = true;
      }

      scanAllFileFields(val, dotProps, name);
    } else if (isArray(val) || isPlainObject(val)) {
      scanAllFileFields(val, dotProps, name);
    }
  });
}

/**
 * Collect file item in posting form values
 * get string[] of path by dot props. etc "attachments.0, attachments.1, avatar"
 *
 * @param {Object} values - form submit values
 * @param {String[]} dotProps - temporary array contain list of keys
 * @param {String} parent - optional, do not use this directly
 * @returns
 */
export default function collectFileItemDotFeed(values: any, parent?: string) {
  const dotProps = {};
  scanAllFileFields(values, dotProps, parent);

  return Object.keys(dotProps);
}
