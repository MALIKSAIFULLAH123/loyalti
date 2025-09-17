/**
 * @type: layoutBlockFeature
 * name: extraListProps
 */

export default function extraItemProps() {
  return {
    component: 'CollapseContainer',
    name: 'extraListProps',
    label: 'Navigation',
    elements: {
      asModal: {
        component: 'Checkbox',
        name: 'itemLinkProps.asModal',
        label: "Open as modal when user clicks on item's title"
      }
    }
  };
}
