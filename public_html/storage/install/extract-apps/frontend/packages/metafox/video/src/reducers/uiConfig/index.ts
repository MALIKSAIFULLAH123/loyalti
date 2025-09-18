import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'videos',
      icon: 'ico-videocam',
      to: '/video'
    }
  },
  sidebarSearch: {
    placeholder: 'Search Videos'
  },
  sidebarCategory: {
    title: 'categories',
    dataSource: { apiUrl: '/video-category' },
    href: '/video/category'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/video',
          label: 'Home',
          testid: 'landing',
          tab: 'landing',
          icon: 'ico-videocam-o'
        },
        {
          to: '/video/all',
          label: 'All Videos',
          tab: 'all',
          testid: 'all',
          icon: 'ico-hashtag'
        },
        {
          to: '/video/my',
          label: 'My Videos',
          tab: 'my',
          testid: 'my',
          icon: 'ico-user-man-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/video/friend',
          // eslint-disable-next-line quotes
          label: "Friends' Videos",
          tab: 'friend',
          // eslint-disable-next-line quotes
          testid: 'friend',
          icon: 'ico-user1-two-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/video/share',
          label: 'Upload Video',
          buttonProps: {
            fullWidth: true,
            color: 'primary',
            variant: 'contained'
          },
          testid: 'add',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        }
      ]
    }
  }
};

export default initialState;
