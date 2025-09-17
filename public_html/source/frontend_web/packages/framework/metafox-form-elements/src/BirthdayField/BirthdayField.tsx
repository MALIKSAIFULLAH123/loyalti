/**
 * @type: formElement
 * name: form.element.Birthday
 * chunkName: datePicker
 */
import loadable from '@loadable/component';

const BirthDayPicker = loadable(
  () =>
    import(
      /* webpackChunkName: "date.picker" */
      './BirthDay'
    )
);

export default BirthDayPicker;
