import { SelectItem } from '@metafox/core';
import { BlockViewProps, useGlobal, useSession } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Loading } from '@metafox/ui';
import { List, styled, Typography } from '@mui/material';
import * as React from 'react';
import { AppState } from '../../types';

const ListStyled = styled(List, {
  shouldForwardProp: props => props !== 'isMobile'
})<{ isMobile?: boolean }>(({ theme, isMobile }) => ({
  ...(isMobile && {
    '.MuiListItemText-root': {
      width: '50%'
    },
    '.MuiInputBase-root': {
      width: '50%',
      textAlign: 'end',
      '.MuiSelect-select': {
        whiteSpace: 'unset'
      }
    }
  })
}));

export type Props = BlockViewProps & AppState['profilePrivacy'];

export default function GeneralSettings({ data, title }: Props) {
  const classes = {};
  const { dispatch, useIsMobile, i18n } = useGlobal();
  const { user } = useSession();

  const isMobile = useIsMobile();

  React.useEffect(() => {
    dispatch({
      type: 'setting/profilePrivacy/FETCH',
      payload: { id: user.id }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onChanged = (value: number, var_name: string) => {
    dispatch({
      type: 'setting/profilePrivacy/UPDATE',
      payload: { var_name, value }
    });
  };

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Typography variant="body1" paragraph>
          {i18n.formatMessage({ id: 'customize_users_interact_your_profile' })}
        </Typography>
        <ListStyled disablePadding isMobile={isMobile}>
          {data ? (
            data.map(item => (
              <SelectItem
                classes={classes}
                onChanged={onChanged}
                item={item}
                key={item.var_name}
              />
            ))
          ) : (
            <Loading related center />
          )}
        </ListStyled>
      </BlockContent>
    </Block>
  );
}
