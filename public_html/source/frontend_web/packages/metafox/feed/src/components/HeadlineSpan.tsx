import { styled } from '@mui/material';

export const AvatarWrapper = styled('div', { name: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  color: theme.palette.text.secondary
}));

export default HeadlineSpan;
