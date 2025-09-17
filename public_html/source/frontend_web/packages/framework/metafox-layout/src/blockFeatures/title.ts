/**
 * @type: layoutBlockFeature
 * name: title
 */

export default function featureCreator({ disabled }) {
  return {
    name: 'title',
    component: 'Text',
    variant: 'outlined',
    maxLength: 100,
    label: 'Title',
    fullWidth: true,
    showWhen: ['and', ['falsy', 'noHeader'], ['truthy', !disabled['title']]]
  };
}
