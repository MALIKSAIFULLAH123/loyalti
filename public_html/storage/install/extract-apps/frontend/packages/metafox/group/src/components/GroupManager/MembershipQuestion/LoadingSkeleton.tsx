/**
 * @type: skeleton
 * name: group.itemView.membershipQuestionCard.skeleton
 * chunkName: group
 */
import { ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { Box, CardActions, Divider, Skeleton, styled } from '@mui/material';
import * as React from 'react';

const ItemStyled = styled(Box)(({ theme }) => ({
  paddingLeft: theme.spacing(1),
  display: 'flex',
  flexDirection: 'row',
  alignItems: 'center'
}));

export default function LoadingSkeleton({ wrapAs, wrapProps }) {
  return (
    <ItemView testid="loadingSkeleton" wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemText>
        <ItemTitle>
          <Skeleton variant="text" width="60%" height={28} />
        </ItemTitle>
      </ItemText>
      <Box>
        <ItemStyled>
          <Skeleton variant="rectangular" width={20} height={20} />
          <Skeleton variant="text" width={150} height={28} sx={{ ml: 1 }} />
        </ItemStyled>
        <Divider sx={{ my: 1, pl: 1 }} />
      </Box>
      <Box>
        <ItemStyled>
          <Skeleton variant="rectangular" width={20} height={20} />
          <Skeleton variant="text" width={150} height={28} sx={{ ml: 1 }} />
        </ItemStyled>
        <Divider sx={{ mt: 1, pl: 1 }} />
      </Box>

      <CardActions sx={{ p: 0 }}>
        <Skeleton variant="text" width={70} height={50} />
        <Skeleton variant="text" width={70} height={50} />
      </CardActions>
    </ItemView>
  );
}
