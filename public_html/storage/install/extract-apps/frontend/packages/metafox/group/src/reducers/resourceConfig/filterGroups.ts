import { FormSchemaShape } from '@metafox/form';

const filterGroups: FormSchemaShape = {
  component: 'Form',
  action: '/group/search',
  elements: {
    search: {
      component: 'searchBox',
      name: 'q',
      placeholder: 'Search groups'
    },
    sorts: {
      name: 'sort',
      component: 'Select',
      label: 'Sort',
      variant: 'outlined',
      margin: 'dense',
      fullWidth: true,
      options: [
        { label: 'Recent', value: 'lasted' },
        { label: 'Popular', value: 'most_member' }
      ]
    },
    when: {
      name: 'when',
      component: 'Select',
      label: 'When',
      variant: 'outlined',
      margin: 'dense',
      fullWidth: true,
      options: [
        { label: 'All', value: 'all' },
        { label: 'This Month', value: 'this_month' },
        { label: 'This Week', value: 'this_week' },
        { label: 'Today', value: 'today' }
      ]
    },
    category_id: {
      name: 'category_id',
      component: 'filterCategory',
      label: 'Categories',
      apiUrl: '/group-type'
    }
  }
};

export default filterGroups;
