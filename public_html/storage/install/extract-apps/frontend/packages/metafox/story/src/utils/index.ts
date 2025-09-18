import moment from 'moment';

export function checkImageError(url, callback) {
  const tester = new Image();

  tester.onload = () => {
    callback && callback(false);
  };
  tester.onerror = () => {
    callback && callback(true);
  };
  tester.src = url;
}

export const readFile = (file: File) => {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.addEventListener('load', () => resolve(reader.result), false);
    reader.readAsDataURL(file);
  });
};

export const mappingRotate = {
  0: 'rotate(0deg) translate(0, 0)',
  90: 'rotate(90deg) translate(0%, -100%)',
  180: 'rotate(180deg) translate(-100%, -100%)',
  270: 'rotate(270deg) translate(-100%, 0%)'
};

export const roundNumber = (number, original) => {
  // compare with 2px and -2px
  if (original && Math.abs(number - original) <= 2) return original;

  return number;
};

export const converStartDate = date => {
  const _date = moment(date);

  if (!_date.isValid()) return date;

  const result = moment(date).local();
  result.set({ hour: 0, minute: 0, second: 0, millisecond: 0 });

  return result?.toISOString();
};

export const converEndDate = date => {
  const _date = moment(date);

  if (!_date.isValid()) return date;

  const data = new Date(date);
  data.setHours(23, 59, 59, 999);

  return data?.toISOString();
};
