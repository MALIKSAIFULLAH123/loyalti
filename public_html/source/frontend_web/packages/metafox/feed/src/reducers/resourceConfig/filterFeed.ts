import { FormSchemaShape } from '@metafox/form';

const formSchema: FormSchemaShape = {
  component: 'Form',
  action: '/hashtag/search',
  elements: {
    search: {
      component: 'searchBox',
      name: 'hashtag',
      placeholder: 'Search'
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
    from: {
      name: 'from',
      component: 'Select',
      label: 'From',
      variant: 'outlined',
      margin: 'dense',
      fullWidth: true,
      options: [
        { label: 'All', value: 'all' },
        { label: 'Users', value: 'user' },
        { label: 'Pages', value: 'page' },
        { label: 'Groups', value: 'group' }
      ]
    },
    switch: {
      component: 'switch',
      name: 'related_comment_friend_only',
      label: 'Show results from friend',
      labelPlacement: 'start',
      fullWidth: true
    }
  }
};
export default formSchema;
