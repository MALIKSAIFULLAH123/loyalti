import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'live_videos',
      icon: 'ico-play-circle-o',
      to: '/live-video'
    }
  },
  sidebarSearch: {
    placeholder: 'search'
  },
  menus: {}
};

export default initialState;
