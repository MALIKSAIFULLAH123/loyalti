import { INTERESTED, NOT_INTERESTED, ON_GOING } from '../constants';

export const mappingRSVP = (rsvp: number) => {
  let label = 'interested';
  let icon = 'ico-calendar-star-o';

  switch (rsvp) {
    case INTERESTED:
      label = 'interested';
      icon = 'ico-calendar-star';
      break;
    case ON_GOING:
      label = 'going';
      icon = 'ico-check-circle-o';
      break;
    case NOT_INTERESTED:
      label = 'not_interested';
      icon = 'ico-calendar-star-o';
      break;
  }

  return { label, icon };
};
