/**
 * @type: formElement
 * name: form.element.Time
 * chunkName: datePicker
 */

import loadable from '@loadable/component';

const TimePicker = loadable(
  () => import(/* webpackChunkName: "date.picker" */ './TimePicker')
);

export default TimePicker;
