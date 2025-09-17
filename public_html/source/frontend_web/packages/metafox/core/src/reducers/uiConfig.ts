const footerMenu = {
  items: [
    { label: 'Privacy', to: '/policy', testid: 'privacy' },
    { label: 'Terms', to: '/term-of-use', testid: 'terms' }
  ]
};

const initialState = {
  menus: {
    footerMenu
  },
  copyright: 'MetaFox © 2022'
};

export default initialState;
