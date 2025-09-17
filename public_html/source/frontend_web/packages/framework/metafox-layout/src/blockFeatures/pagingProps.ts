/**
 * @type: layoutBlockFeature
 * name: pagingProps
 */

import { BlockFeatureCreatorConfig } from '@metafox/layout/types';

export default function featureCreator({
  disabled,
  manager
}: BlockFeatureCreatorConfig) {
  return {
    component: 'CollapseContainer',
    label: 'Pagination',
    elements: {
      canLoadMore: {
        name: 'canLoadMore',
        component: 'Checkbox',
        label: 'Load more items when user scroll to the end',
        margin: 'dense',
        disabled: disabled['canLoadMore']
      },
      maxPageNumber: {
        component: 'Text',
        variant: 'outlined',
        labelProps: { shrink: true },
        margin: 'dense',
        fullWidth: true,
        defaultValue: 0,
        name: 'maxPageNumber',
        label: 'Max number of pages to display',
        disabled: disabled['maxPageNumber'],
        showWhen: ['truthy', 'canLoadMore']
      },
      numberOfItemsPerPage: {
        component: 'Text',
        variant: 'outlined',
        labelProps: { shrink: true },
        margin: 'dense',
        fullWidth: true,
        name: 'numberOfItemsPerPage',
        label: 'Number of items per page',
        disabled: disabled['numberOfItemsPerPage'],
        showWhen: ['truthy', 'canLoadMore']
      },
      canLoadSmooth: {
        name: 'canLoadSmooth',
        component: 'Checkbox',
        label: 'Append loading elements at the end of the listing.',
        margin: 'dense',
        disabled: disabled['canLoadSmooth']
      },
      hasSearchBox: {
        name: 'hasSearchBox',
        component: 'Checkbox',
        label: 'Enable Search Box?',
        margin: 'dense',
        disabled: disabled['hasSearchBox']
      },
      displayLimit: {
        component: 'Text',
        variant: 'outlined',
        labelProps: { shrink: true },
        margin: 'dense',
        fullWidth: true,
        defaultValue: '10',
        name: 'displayLimit',
        label: 'Number of items to display',
        disabled: disabled['displayLimit'],
        showWhen: ['falsy', 'canLoadMore']
      },
      displayRowsLimit: {
        component: 'Text',
        variant: 'outlined',
        labelProps: { shrink: true },
        margin: 'dense',
        fullWidth: true,
        name: 'displayRowsLimit',
        label: 'Number of rows item to display',
        disabled: disabled['displayRowsLimit'],
        showWhen: ['falsy', 'canLoadMore']
      }
    }
  };
}
