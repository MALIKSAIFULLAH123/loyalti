/**
 * @type: layoutBlockFeature
 * name: order
 */

export default function featureCreator({ disabled }) {
  return {
    name: 'blockOrder',
    component: 'Text',
    type: 'number',
    variant: 'outlined',
    margin: 'normal',
    maxLength: 3,
    label: 'Order Position',
    fullWidth: true
  };
}
