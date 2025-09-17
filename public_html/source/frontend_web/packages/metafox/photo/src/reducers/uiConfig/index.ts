import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'photos',
      icon: 'ico-photos-alt',
      to: '/photo'
    }
  },
  menus: {},
  sidebarSearch: {
    placeholder: 'Search photos'
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/photo-category' },
    href: '/photo/category',
    title: 'categories'
  }
};

export default initialState;
