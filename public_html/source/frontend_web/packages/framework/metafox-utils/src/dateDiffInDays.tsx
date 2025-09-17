import moment from 'moment';
type ParamDate = moment.Moment | string;
const dateDiffInDays = (date1: ParamDate, date2?: ParamDate) => {
  const newDate = moment(date2).set({
    year: 0
  });
  const endDate = moment(date1).set({
    year: 0
  });

  return endDate.diff(newDate, 'days');
};

export default dateDiffInDays;
