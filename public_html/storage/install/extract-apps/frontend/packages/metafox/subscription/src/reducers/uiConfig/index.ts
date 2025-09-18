import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'subscriptions',
      icon: 'ico-box-o'
    }
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/subscribe',
          label: 'Packages',
          tab: 'package',
          testid: 'packages'
        },
        {
          to: '/subscribe/list',
          label: 'My Subscriptions',
          tab: 'my',
          testid: 'my subscription',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/subscribe/compare',
          label: 'Plan Comparisons',
          tab: 'compare',
          testid: 'plan comparisons'
        }
      ]
    }
  }
};

export default initialState;
