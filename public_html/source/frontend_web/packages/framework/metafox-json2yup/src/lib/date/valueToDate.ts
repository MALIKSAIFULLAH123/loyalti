import { fromUnix } from '../../lib/date/index';

const valueToDate = (value: string | number): Date => {
  if (Number.isInteger(value)) {
    return fromUnix(value as number);
  } else if (typeof value === 'string') {
    return new Date(value);
  }

  return null;
};
export default valueToDate;
