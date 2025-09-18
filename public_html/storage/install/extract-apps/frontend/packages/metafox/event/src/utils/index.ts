export { isEventEnd, mappingTimeDisplay } from './time';
export { mappingRSVP } from './mappingRSVP';

export const getTopPosition = x => (`${x}`.includes('%') ? x : undefined);
