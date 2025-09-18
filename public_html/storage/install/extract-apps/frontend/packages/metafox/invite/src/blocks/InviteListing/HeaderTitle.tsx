import React from 'react';
import { useGlobal, useResourceMenu } from '@metafox/framework';
import {
  styled,
  Grid,
  Typography,
  Checkbox,
  Box,
  Tooltip
} from '@mui/material';
import { isEmpty } from 'lodash';
import { APP_INVITE, RESOURCE_INVITE } from '@metafox/invite/constants';
import { LineIcon } from '@metafox/ui';

const TitleStyled = styled(Grid, { name: 'TitleStyled' })(({ theme }) => ({
  display: 'flex',
  padding: theme.spacing(3, 0, 2)
}));

const HeaderStyled = styled(Typography, { name: 'HeaderStyled' })(
  ({ theme }) => ({
    fontWeight: theme.typography.fontWeightMedium
  })
);

const CheckboxStyled = styled(Box)(({ theme }) => ({
  textAlign: 'center'
}));

const LineIconStyled = styled(LineIcon)(({ theme }) => ({
  marginLeft: theme.spacing(1.5),
  color: theme.palette.text.secondary
}));

const WrapHeaderTitle = styled(Box)(({ theme }) => ({
  display: 'flex',
  flex: 1,
  alignItems: 'center',
  padding: theme.spacing(0, 2),
  marginLeft: theme.spacing(2)
}));

const gridTitle = [
  {
    label: 'email_phone',
    grid: 7
  },
  {
    label: 'status',
    grid: 1.5
  },
  {
    label: 'invited_date',
    grid: 3
  },
  {
    label: null,
    grid: 0.5
  }
];

const HeaderTitle = () => {
  const { i18n, useBatchSelectContext } = useGlobal();
  const [indeterminate, setIndeterminate] = React.useState(false);
  const [checked, setChecked] = React.useState(false);

  const menus: any = useResourceMenu(
    APP_INVITE,
    RESOURCE_INVITE,
    'itemActionMenu'
  );

  const valueContext = useBatchSelectContext();

  const {
    pagingData,
    handleSelectAll,
    pagingIds,
    checkedList,
    handleActionName
  } = valueContext;

  const handleCheckboxChange = () => {
    handleSelectAll();
    setChecked(true);
  };

  React.useEffect(() => {
    if (checkedList.length) {
      setIndeterminate(true);
    } else {
      setIndeterminate(false);
    }

    if (
      !isEmpty(pagingData) &&
      checkedList.length &&
      pagingIds.length === checkedList.length
    ) {
      setChecked(true);
    } else {
      setChecked(false);
    }
  }, [pagingData, pagingIds, checkedList]);

  const menuItems = menus?.items || [];

  return (
    <TitleStyled container alignItems="center">
      <CheckboxStyled>
        <Checkbox
          disabled={!pagingData?.ids?.length}
          checked={checked}
          onChange={handleCheckboxChange}
          indeterminate={!checked && indeterminate}
          color="primary"
          size="medium"
        />
      </CheckboxStyled>
      {checkedList.length ? (
        <WrapHeaderTitle>
          <Grid item xs={10} container alignItems="center">
            <HeaderStyled variant="h5">
              {i18n.formatMessage(
                { id: 'total_selected' },
                { total: checkedList.length }
              )}
            </HeaderStyled>
            {menuItems.map(item => (
              <Tooltip title={item.label} key={item.value}>
                <LineIconStyled
                  icon={item.icon}
                  onClick={() => handleActionName(item.value)}
                />
              </Tooltip>
            ))}
          </Grid>
        </WrapHeaderTitle>
      ) : (
        <WrapHeaderTitle>
          {gridTitle.map((item, index) => (
            <Grid item key={index} xs={item.grid}>
              <Typography variant="h5">
                {item.label && i18n.formatMessage({ id: item.label })}
              </Typography>
            </Grid>
          ))}
        </WrapHeaderTitle>
      )}
    </TitleStyled>
  );
};

export default HeaderTitle;
