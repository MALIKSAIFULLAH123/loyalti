const adminSubMenu = {
  items: [
    {
      icon: 'ico-language',
      value: '@core/codeGenerate/addTranslation',
      label: 'add_translation',
      as: 'link',
      name: 'code',
      showWhen: ['eq', 'setting.app.env', 'local']
    },
    {
      name: 'rebuild',
      icon: 'ico-refresh-o',
      to: '/layout/build/wizard',
      label: 'rebuild_site',
      title: 'rebuild_site',
      as: 'link'
    },
    {
      name: 'cache',
      icon: 'ico-noun-broom',
      value: '@admin/showCacheDialog',
      label: 'clear_cache',
      title: 'clear_cache',
      as: 'link'
    },
    {
      icon: 'ico-code',
      value: '@core/codeGenerate/show',
      label: 'code_generator',
      as: 'link',
      name: 'code',
      showWhen: ['eq', 'setting.app.env', 'local']
    },
    {
      icon: 'ico-bell-o',
      to: '/notification',
      label: 'notifications',
      as: 'popover',
      name: 'new_notification',
      content: {
        component: 'notification.ui.notificationPopper'
      }
    },
    { as: 'divider' },
    {
      icon: '',
      as: 'adminUser',
      name: 'adminUser',
      target: '_blank'
    },
    {
      icon: 'ico-shutdown',
      as: 'link',
      name: 'logout',
      to: '/logout',
      title: 'logout',
      label: 'logout'
    },
    {
      as: 'divider',
      name: 'divider'
    },
    {
      icon: 'ico-external-link',
      value: 'viewSite',
      as: 'viewSite',
      label: 'view_site',
      name: 'viewSite'
    }
  ]
};

export default adminSubMenu;
