/**
 * @type: layoutBlockFeature
 * name: headerActions
 */

import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function featureCreator({
  disabled,
  theme
}: BlockFeatureCreatorConfig) {
  return {
    component: 'CollapseContainer',
    label: 'Header Actions',
    showWhen: ['falsy', 'noHeader'],
    elements: {
      margin: {
        component: 'MarginSpaceGroup',
        name: 'headerActionsStyle',
        prefix: 'headerActionsStyle',
        description: 'Space around header actions',
        margin: 'normal'
      }
    }
  };
}
