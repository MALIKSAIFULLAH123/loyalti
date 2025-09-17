import * as React from 'react';
import { Box, styled, Typography } from '@mui/material';
import {
  APP_GETTING_STARTED,
  RESOURCE_TODO_LIST,
  PAGING_ID_TODO_LIST
} from '@metafox/gettingstarted';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';

const ListStyled = styled(Box)(({ theme }) => ({}));

const Root = styled(Box)(({ theme }) => ({
  width: '100%',
  border: '10px',
  borderColor: theme.palette.border.secondary,
  padding: theme.spacing(2),
  borderStyle: 'double',
  height: '100%',
  borderRadius: theme.spacing(1),
  position: 'relative',
  '& .Scrollbar-view': {
    paddingTop: theme.spacing(2)
  }
}));

export type Props = {
  closeTodo: () => void;
};

export default function TodoList({ closeTodo }: Props) {
  const { i18n, ListView } = useGlobal();

  return (
    <Root>
      <ListStyled>
        <Typography variant="h3" sx={{ textAlign: 'center', mb: 1 }}>
          {i18n.formatMessage({ id: 'check_list' })}
        </Typography>
        <ScrollContainer autoHeightMax={'50vh'} autoHide autoHeight>
          <ListView
            moduleName={APP_GETTING_STARTED}
            resourceName={RESOURCE_TODO_LIST}
            actionName={'showTodoList'}
            errorPage="hide"
            itemView={'gettingStarted.itemView.todoItem'}
            numberOfItemsPerPage={20}
            cachedEmpty={false}
            slotName={''}
            handleActionItem={closeTodo}
            pagingId={PAGING_ID_TODO_LIST}
          />
        </ScrollContainer>
      </ListStyled>
    </Root>
  );
}
