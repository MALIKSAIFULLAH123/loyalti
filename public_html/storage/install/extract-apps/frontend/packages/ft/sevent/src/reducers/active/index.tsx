import produce from 'immer';

export default produce((draft: any, action) => {
  switch (action.type) {
    case 'sevent/active':
      draft = action.payload;
      break;
    default:
      draft = '';
  }
  
  return draft;

}, '');
