import { createSearchParams } from 'react-router-dom';

export const encodeSearchParams = params => createSearchParams(params);

const decodeSearchParams = searchParams => {
  return [...searchParams.entries()].reduce((acc, [key, val]) => {
    try {
      return {
        ...acc,
        [key]: JSON.parse(val)
      };
    } catch {
      return {
        ...acc,
        [key]: val
      };
    }
  }, {});
};

export default decodeSearchParams;
