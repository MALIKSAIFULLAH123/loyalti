import produce from 'immer';

export default produce((draft: any, action) => {
  switch (action.type) {
    case 'event/active':
      draft = action.payload;
      break;
  }

  return draft;
}, '');
