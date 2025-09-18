import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'polls',
      icon: 'ico-barchart',
      to: '/poll'
    }
  },
  sidebarSearch: { placeholder: 'Search polls' },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/poll',
          label: 'Home',
          icon: 'ico-barchart-o',
          testid: 'landing',
          tab: 'landing'
        },
        {
          to: '/poll/all',
          label: 'All Polls',
          icon: 'ico-hashtag',
          tab: 'all',
          testid: 'all'
        },
        {
          to: '/poll/my',
          label: 'My Polls',
          testid: 'my',
          tab: 'my',
          icon: 'ico-user-man-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/poll/friend',
          // eslint-disable-next-line quotes
          label: 'friend_s_polls',
          // eslint-disable-next-line quotes
          testid: 'friend',
          tab: 'friend',
          icon: 'ico-user1-two-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/poll/pending',
          label: 'Pending Polls',
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
          to: '/poll/add',
          label: 'Add New Poll',
          testid: 'add',
          tab: 'add',
          showWhen: ['and', ['truthy', 'session.loggedIn']],
          buttonProps: {
            fullWidth: true,
            color: 'primary',
            variant: 'contained'
          }
        }
      ]
    }
  }
};

export default initialState;
