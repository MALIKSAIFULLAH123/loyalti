import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'Home',
      to: '/',
      icon: 'ico-home-alt'
    }
  },
  sidebarSearch: {
    placeholder: 'Search'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/hashtag/search',
          label: 'All',
          tab: 'all',
          testid: 'manage-hidden'
        },
        {
          to: '/hashtag/search',
          label: 'Feed Post',
          tab: 'post',
          testid: 'manage-hidden'
        },
        {
          to: '/hashtag/search',
          label: 'Photo',
          tab: 'photo',
          testid: 'manage-hidden'
        },
        {
          to: '/hashtag/search',
          label: 'Video',
          tab: 'video',
          testid: 'manage-hidden'
        }
      ]
    }
  }
};

export default initialState;
