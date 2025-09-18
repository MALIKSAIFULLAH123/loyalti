import { createSlice } from '@reduxjs/toolkit';

type State = Partial<{ total: number }>;

export const announcementStatistic = createSlice({
  name: 'announcementStatistic',
  initialState: { total: 0 },
  reducers: {
    decreaseTotalAnnouncement: (state: State) => {
      state.total = state.total - 1;
    },
    updateTotalAnnouncement: (state: State, action) => {
      state.total = action.payload;
    }
  }
});

export const { decreaseTotalAnnouncement, updateTotalAnnouncement } =
  announcementStatistic.actions;

export default announcementStatistic.reducer;
