import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'quizzes',
      icon: 'ico-check-square-o3',
      to: '/quiz'
    }
  },
  sidebarSearch: {
    placeholder: 'Search Quizzes'
  },
  menus: {}
};

export default initialState;
