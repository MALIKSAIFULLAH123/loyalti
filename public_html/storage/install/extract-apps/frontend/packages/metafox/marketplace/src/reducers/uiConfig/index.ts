import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'marketplace',
      icon: 'ico-shopbasket',
      to: '/marketplace'
    }
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/marketplace-category' },
    title: 'categories',
    href: '/marketplace/category'
  },
  sidebarSearch: {
    placeholder: 'Search'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/marketplace',
          label: 'Home',
          tab: 'landing',
          icon: 'ico-newspaper-alt-o',
          testid: 'landing'
        },
        {
          to: '/marketplace/all',
          label: 'All Listings',
          tab: 'all',
          icon: 'ico-hashtag',
          testid: 'all'
        },
        {
          to: '/marketplace/invite',
          label: 'Listing Invites',
          tab: 'invite',
          testid: 'invite',
          icon: 'ico-price-tag-o'
        },
        {
          to: '/marketplace/friend',
          // eslint-disable-next-line quotes
          label: "Friends' Listings",
          tab: 'friend',
          // eslint-disable-next-line quotes
          testid: 'friends',
          icon: 'ico-user1-two-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/marketplace/my',
          label: 'My Listings',
          tab: 'my',
          testid: 'my',
          icon: 'ico-user-man-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/marketplace/history',
          label: 'History',
          tab: 'history',
          testid: 'history',
          icon: 'ico-calendar-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/marketplace/pending',
          label: 'Pending Listings',
          tab: 'pending',
          testid: 'pending',
          icon: 'ico-clock-o',
          showWhen: [
            'and',
            ['truthy', 'session.loggedIn'],
            ['eq', 'session.user.user_group_id', '1']
          ]
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/marketplace/add',
          label: 'Add Listing',
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
