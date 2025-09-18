import { useGlobal } from '@metafox/framework';
import { StoryItemProps } from '@metafox/story/types';
import { LineIcon } from '@metafox/ui';
import { Box, Typography, styled } from '@mui/material';
import React from 'react';

const name = 'commentItem';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  width: '260px',
  minWidth: '260px',
  display: 'flex',
  alignItems: 'center',
  border: '1px solid #fff',
  padding: theme.spacing(1.5),
  borderRadius: 40,
  cursor: 'pointer',
  maxHeight: '44px'
}));

const ViewTextStyled = styled(Typography)(({ theme }) => ({
  color: '#fff',
  fontWeight: 'normal'
}));
const ToggleIconStyled = styled(LineIcon)(({ theme }) => ({
  color: '#fff',
  marginRight: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(20),
  fontWeight: theme.typography.fontWeightBold
}));

interface IProps {
  item: StoryItemProps;
  onClick?: () => void;
}

function CommentItem({ onClick, item }: IProps) {
  const { i18n } = useGlobal();

  return (
    <RootStyled onClick={onClick}>
      <ToggleIconStyled icon="ico-comment" />
      <ViewTextStyled variant="h5" color={'text.primary'}>
        {i18n.formatMessage({
          id: item?.extra?.can_comment
            ? 'write_comment_three_dots'
            : 'read_the_comments'
        })}
      </ViewTextStyled>
    </RootStyled>
  );
}

export default CommentItem;
