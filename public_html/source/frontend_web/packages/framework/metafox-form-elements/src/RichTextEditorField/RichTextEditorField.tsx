/**
 * @type: formElement
 * name: form.element.Editor
 * chunkName: formExtras
 */
import loadable from '@loadable/component';

const EditorField = loadable(
  () => import(/* webpackChunkName: "FormEditor" */ './EditorField')
);

export default EditorField;
