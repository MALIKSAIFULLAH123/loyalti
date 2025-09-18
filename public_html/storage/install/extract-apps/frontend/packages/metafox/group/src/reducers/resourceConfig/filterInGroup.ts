import { FormSchemaShape } from '@metafox/form';

const formSchema: FormSchemaShape = {
  component: 'Form',
  action: '/group/search/:id/',
  elements: {
    search: {
      component: 'searchBox',
      name: 'q',
      placeholder: 'Search'
    },
    sorts: {
      name: 'sort',
      component: 'Select',
      label: 'Select type',
      variant: 'outlined',
      margin: 'dense',
      fullWidth: true,
      options: [
        { label: 'Recent', value: 'latest' },
        { label: 'Most Viewed', value: 'most_viewed' },
        { label: 'Most Liked', value: 'most_member' },
        { label: 'Most Discussed', value: 'most_discussed' }
      ]
    },
    when: {
      name: 'when',
      component: 'Select',
      label: 'Select type',
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
    switch: {
      component: 'switch',
      name: 'related_comment_friend_only',
      label: 'Toggle Setting',
      labelPlacement: 'start',
      fullWidth: true
    }
  }
};
export default formSchema;
