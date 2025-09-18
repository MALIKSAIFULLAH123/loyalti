import moment from 'moment';

export const isEventEnd = (endDate: string) => {
  const endTime = moment(endDate);
  const now = moment(new Date());

  return endTime.diff(now) < 0;
};

export const mappingTimeDisplay = (
  start: string,
  end: string,
  is24h: boolean
) => {
  const startTimeDateString = moment(new Date(start).toDateString());
  const endTimeDateString = moment(new Date(end).toDateString());
  const endTime = moment(new Date(end));
  const startTime = moment(new Date(start));

  const isDifferentDay = endTimeDateString.diff(startTimeDateString, 'days');

  const timeArr = [];

  if (!endTime) {
    timeArr[0] = moment(startTime).format('LT');
  }

  if (!isDifferentDay) {
    if (is24h) {
      timeArr[0] = moment(startTime).format('HH:mm');
      timeArr[1] = moment(endTime).format('HH:mm');
    } else {
      timeArr[0] = moment(startTime).format('hh:mm A');
      timeArr[1] = moment(endTime).format('hh:mm A');
    }
  }

  if (isDifferentDay) {
    if (is24h) {
      timeArr[0] = moment(startTime).format('HH:mm');
      timeArr[1] = moment(endTime).format('MMMM DD YYYY, HH:mm');
    } else {
      timeArr[0] = moment(startTime).format('hh:mm A');
      timeArr[1] = moment(endTime).format('MMMM DD YYYY, hh:mm A');
    }
  }

  return [timeArr[0], timeArr[1]];
};
