import { FormSchemaShape } from '@metafox/form';

const formSchema: FormSchemaShape = {
  component: 'Form',
  action: '/music/album/search',
  elements: {
    search: {
      component: 'searchBox',
      name: 'q',
      placeholder: 'Search albums'
    },
    sorts: {
      name: 'sort',
      component: 'Select',
      label: 'Sort',
      variant: 'outlined',
      margin: 'dense',
      fullWidth: true,
      options: [
        { label: 'Recent', value: 'latest' },
        { label: 'Most Viewed', value: 'most_viewed' },
        { label: 'Most Liked', value: 'most_liked' },
        { label: 'Most Discussed', value: 'most_discussed' }
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
    }
  }
};
export default formSchema;
