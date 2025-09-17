// https://gist.github.com/jeneg/9767afdcca45601ea44930ea03e0febf#gistcomment-3012251

const get = (value: any, path: string, defaultValue: any = null) => {
  const objectValue = String(path)
    .split('.')
    .reduce((acc, v) => {
      try {
        // eslint-disable-next-line no-param-reassign
        acc = acc[v];
      } catch (e) {
        /* istanbul ignore next */
        return defaultValue;
      }

      return acc;
    }, value);

  if (objectValue == null && defaultValue != null) {
    return defaultValue;
  }

  return objectValue;
};

export default get;
