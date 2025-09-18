import { AppUIConfig } from '@metafox/framework';
import { APP_PAGE } from '@metafox/pages';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'pages',
      icon: 'ico-flag-waving',
      to: `/${APP_PAGE}`
    },
    backToProfile: {
      title: 'back_to_page',
      icon: 'ico-user1-three-o'
    }
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/page-type' },
    title: 'categories',
    href: '/page/type'
  },
  sidebarSearch: {
    placeholder: 'search_pages'
  },
  menus: {
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
    },
    actionMenuProfilePoll: {
      items: [
        {
          as: 'poll.ui.addPollButton',
          testid: 'addPoll'
        }
      ]
    },
    actionMenuProfileMarketplace: {
      items: [
        {
          as: 'marketplace.ui.addMarketplaceButton',
          testid: 'addMarketplace'
        }
      ]
    },
    actionMenuProfileQuiz: {
      items: [
        {
          as: 'quiz.ui.addQuizButton',
          testid: 'addMQuizzes'
        }
      ]
    },
    sidebarMenu: {
      items: [
        {
          to: '/page',
          label: 'Home',
          testid: 'landing',
          tab: 'landing',
          icon: 'ico-flag-waving-o'
        },
        {
          to: '/page/all',
          label: 'All Pages',
          testid: 'all',
          tab: 'all',
          icon: 'ico-hashtag'
        },
        {
          to: '/page/my',
          label: 'My Pages',
          testid: 'my',
          tab: 'my',
          icon: 'ico-user-man-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/page/friend',
          // eslint-disable-next-line quotes
          label: "Friends' Pages",
          // eslint-disable-next-line quotes
          testid: 'friend',
          tab: 'friend',
          icon: 'ico-user1-two-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/page/liked',
          // eslint-disable-next-line quotes
          label: 'Likes Pages',
          // eslint-disable-next-line quotes
          testid: 'like',
          tab: 'liked',
          icon: 'ico-thumbup-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/page/invited',
          // eslint-disable-next-line quotes
          label: 'Page Invites',
          // eslint-disable-next-line quotes
          testid: 'invited',
          tab: 'invited',
          icon: 'ico-user1-three-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/page/add',
          label: 'Add Pages',
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
    },
    pageSettingsMenu: {
      items: [
        {
          as: 'sidebarHeading',
          label: 'Page Settings',
          testid: 'page settings '
        },
        {
          to: '/page/settings/:id/info',
          label: 'Page Info',
          tab: 'info',
          testid: 'page info'
        },
        {
          to: '/page/settings/:id/about',
          label: 'About Page',
          tab: 'about',
          testid: 'about page'
        },
        {
          to: '/page/settings/:id/url',
          label: 'Vanity URL',
          tab: 'url',
          testid: 'vanity url'
        },
        {
          as: 'sidebarDivider',
          testid: 'divider'
        },
        {
          as: 'sidebarHeading',
          label: 'Privacy Settings',
          testid: 'privacy settings '
        },
        {
          to: '/page/settings/:id/permissions',
          label: 'Permissions',
          tab: 'landing',
          testid: 'permissions'
        },
        {
          as: 'sidebarDivider',
          testid: 'divider'
        },
        {
          as: 'sidebarHeading',
          label: 'Page Menu Settings',
          testid: 'page menu settings '
        },
        {
          to: '/page/settings/:id/menu',
          label: 'Menu',
          tab: 'landing',
          testid: 'menu'
        },
        {
          as: 'sidebarHeading',
          label: 'Page Messages',
          testid: 'page messages'
        },
        {
          to: '/page/settings/:id/chat',
          label: 'Inbox',
          tab: 'inbox',
          testid: 'inbox'
        }
      ]
    },
    sidebarSearchInPageMenu: {
      items: [
        {
          label: 'Posts',
          tab: 'post',
          testid: 'manage-hidden'
        },
        {
          label: 'Photos',
          tab: 'photo',
          testid: 'manage-hidden'
        },
        {
          label: 'Videos',
          tab: 'video',
          testid: 'manage-hidden'
        }
      ]
    }
  }
};

export default initialState;
