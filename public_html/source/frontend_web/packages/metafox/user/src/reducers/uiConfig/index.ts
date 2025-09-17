import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'members',
      icon: 'ico-user1-three',
      to: '/user'
    }
  },
  sidebarSearch: {
    placeholder: 'Search members'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/user',
          label: 'Home',
          tab: 'landing',
          testid: 'landing',
          icon: 'ico-user1-three-o'
        },
        {
          to: '/user/all',
          label: 'All Members',
          tab: 'all',
          testid: 'all',
          icon: 'ico-hashtag'
        },
        {
          to: '/user/recommend',
          label: 'Members You May Know',
          tab: 'recommend',
          testid: 'recommend',
          icon: 'ico-user2-three-o'
        },
        {
          to: '/user/recent',
          label: 'Recent Active',
          tab: 'recent',
          testid: 'recent',
          icon: 'ico-user2-clock-o'
        },
        {
          to: '/user/featured',
          label: 'Featured Members',
          tab: 'featured',
          testid: 'featured',
          icon: 'ico-diamond-o'
        }
      ]
    },
    settingMenu: {
      items: []
    },
    tabMenuProfilePhoto: {
      items: [
        {
          to: 'photo',
          label: 'Photos',
          tab: 'photo',
          testid: 'photo'
        },
        {
          to: 'album',
          label: 'Albums',
          tab: 'album',
          testid: 'album'
        }
      ]
    },
    actionMenuProfilePhoto: {
      items: [
        {
          as: 'feed.ui.addPhotoButton',
          testid: 'addPhotos'
        }
      ]
    }
  }
};

export default initialState;
