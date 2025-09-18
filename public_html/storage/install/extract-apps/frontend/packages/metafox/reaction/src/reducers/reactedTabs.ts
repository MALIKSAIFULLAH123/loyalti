import produce from 'immer';

export default produce((draft, action) => {
  switch (action.type) {
    case 'reactedTabs/FULFILL': {
      const { identity, data } = action.payload;
      draft[identity] = data;
      break;
    }
  }
}, {});
