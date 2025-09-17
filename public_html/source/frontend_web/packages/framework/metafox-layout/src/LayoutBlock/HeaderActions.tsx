import {
  CtaActionShape,
  useGetItem,
  useGlobal,
  useSession
} from '@metafox/framework';
import { filterShowWhen, when } from '@metafox/utils';
import { SxProps, styled } from '@mui/material/styles';
import React from 'react';
import HeaderAction from './HeaderAction';
import { useBlock } from '@metafox/layout';

const MuiHeaderActions = styled('div', {
  name: 'GeneralBlock',
  slot: 'headerActions',
  shouldForwardProp: prop => prop !== 'slotName'
})<{ slotName: string }>(({ theme, slotName }) => ({
  alignItems: 'center',
  flexDirection: 'row',
  display: 'flex',
  '& .MuiButton-link': {
    fontSize: slotName === 'subside' ? theme.mixins.pxToRem(13) : 'inherit'
  }
}));

export default function HeaderActions({
  actions,
  data,
  ...rest
}: {
  actions: CtaActionShape[];
  data?: any[];
  sx?: SxProps;
}) {
  const { jsxBackend, usePageParams, getAcl, getSetting } = useGlobal();
  const setting = getSetting();
  const session = useSession();
  const { user: authUser } = session;
  const pageParams = usePageParams();
  const item = useGetItem(pageParams?.identity);
  const acl = getAcl();

  const { slotName } = useBlock();

  if (!actions?.length) return null;

  const isAuthUser =
    authUser?.id && pageParams?.id && authUser?.id === parseInt(pageParams.id);

  const condition = {
    pageParams,
    authUser,
    session,
    isAuthUser,
    item,
    acl,
    data,
    setting
  };
  const items = filterShowWhen(actions, condition).map(
    ({ as: c, enabledWhen, ...props }, index) => ({
      component: c ?? HeaderAction,
      props: {
        key: index.toString(),
        ...props,
        disabled: enabledWhen ? !when({ item }, enabledWhen) : false
      }
    })
  );

  return (
    <MuiHeaderActions data-testid="headerActions" slotName={slotName} {...rest}>
      {jsxBackend.render(items)}
    </MuiHeaderActions>
  );
}
