import { FormSchemaShape } from '@metafox/form';

const formSchema: FormSchemaShape = {
  component: 'Form',
  action: '/event/search',
  elements: {
    search: {
      component: 'searchBox',
      name: 'q',
      placeholder: 'Search events'
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
        { label: 'Upcoming', value: 'upcoming' },
        { label: 'Ongoing', value: 'ongoing' },
        { label: 'This Week', value: 'this_week' },
        { label: 'Today', value: 'today' }
      ]
    },
    category: {
      name: 'category',
      component: 'filterCategory',
      label: 'Categories',
      apiUrl: '/event-category'
    }
  }
};
export default formSchema;
