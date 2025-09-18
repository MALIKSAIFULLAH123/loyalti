/**
 * @type: dialog
 * name: quiz.dialog.PeoplePlayed
 */

import { useGlobal, useResourceAction } from '@metafox/framework';
import { Dialog, DialogContent, DialogTitle } from '@metafox/dialog';
import { ScrollContainer } from '@metafox/layout';
import { Box, styled, Tab as MuiTab, Tabs } from '@mui/material';
import React from 'react';
import {
  APP_QUIZ,
  INDIVIDUALS_TAB,
  RESOURCE_QUIZ,
  SUMMARY_TAB
} from '@metafox/quiz/constant';
import SelectCustom from './SelectCustom';
import IndividualItem from './IndividualItem';
import { camelCase, isEmpty, isString } from 'lodash';
import { compactData } from '@metafox/utils';

export type Props = {
  [key: string]: any;
};

const Tab = styled(MuiTab)(({ theme }) => ({
  height: 50,
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.secondary,
  fontWeight: 'bold',
  borderBottom: '2px solid transparent',
  minWidth: 0,
  padding: 0,
  marginRight: theme.spacing(4),
  '& .Mui-selected': {
    borderBottomColor: theme.palette.primary.main,
    color: theme.palette.primary.main
  },
  '&:hover': {
    color: theme.palette.primary.main
  }
}));

const WrapContentStyled = styled(Box)(({ theme }) => ({
  padding: theme.spacing(0, 2)
}));

export default function ListFriendsDialog({
  dialogTitle,
  questions,
  user,
  userOwner,
  item
}: Props) {
  const { useDialog, ListView, i18n, useGetItem } = useGlobal();
  const { dialogProps } = useDialog();
  const dataSource = useResourceAction(
    APP_QUIZ,
    RESOURCE_QUIZ,
    'viewQuizSummary'
  );
  const [tab, setTab] = React.useState<number>(SUMMARY_TAB);
  const [valueSelect, setValueSelect] = React.useState(null);
  const [value, setValue] = React.useState(questions[0].id);

  const handleSelectValue = (_, newValue) => {
    setValue(newValue.id);
    setValueSelect(newValue);
  };

  const usersOption = (user || [])
    // .filter(item => item.id !== userOwner.id)
    .map((item, idx) => {
      if (isString(item.user)) {
        // eslint-disable-next-line react-hooks/rules-of-hooks
        const data = useGetItem(item.user);

        return { id: data.id, label: data.full_name, ordering: idx };
      }

      return { id: item.user.id, label: item.user.full_name, ordering: idx };
    });

  const questionsOption = React.useMemo(() => {
    return (questions || []).map(item => {
      return { id: item.id, label: item.question, ordering: item.ordering };
    });
  }, [questions]);

  const dataSelect = React.useMemo(() => {
    return {
      [SUMMARY_TAB]: { options: questionsOption },
      [INDIVIDUALS_TAB]: { options: usersOption }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [questions, user, questionsOption, usersOption]);

  const changeTab = (event, val) => {
    setTab(val);

    if (val === SUMMARY_TAB) {
      setValueSelect(questionsOption[0]);
      setValue(questionsOption[0].id);
    } else {
      setValueSelect(usersOption[0]);
      setValue(usersOption[0].id);
    }
  };

  const isViewIndividual = React.useMemo(() => {
    if (isEmpty(user)) {
      return false;
    }

    if (item?.is_owner || item?.extra?.can_moderate) {
      return true;
    }

    return false;
  }, [user, item]);

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <Box data-testid={camelCase(`dialog ${dialogTitle}`)}>
        <DialogTitle>{i18n.formatMessage({ id: dialogTitle })}</DialogTitle>
        <DialogContent variant="fitScroll" sx={{ height: '45vh' }}>
          <WrapContentStyled>
            <Tabs
              value={tab}
              onChange={changeTab}
              indicatorColor="primary"
              textColor="primary"
            >
              <Tab
                disableRipple
                label={i18n.formatMessage({ id: 'summary' }).toUpperCase()}
                data-testid={camelCase('tab_summary')}
              />
              {isViewIndividual ? (
                <Tab
                  disableRipple
                  label={i18n
                    .formatMessage({ id: 'individuals' })
                    .toUpperCase()}
                  data-testid={camelCase('tab_individuals')}
                />
              ) : null}
            </Tabs>

            <SelectCustom
              onSelect={handleSelectValue}
              data={dataSelect[tab]}
              tab={tab}
              isOrtherTab={tab !== SUMMARY_TAB}
              valueSelect={valueSelect}
            />
          </WrapContentStyled>
          <ScrollContainer autoHeightMax={'100%'}>
            <WrapContentStyled sx={{ mb: 2 }}>
              {tab === SUMMARY_TAB ? (
                <ListView
                  canLoadMore
                  dataSource={{
                    apiMethod: dataSource?.apiMethod || 'get',
                    apiUrl: dataSource?.apiUrl,
                    apiParams: compactData(dataSource.apiParams, {
                      id: value
                    })
                  }}
                  pagingId={`/quiz/view-plays/tab=${tab}/value=${value}`}
                  prefixPagingId="/quiz/view-plays"
                  itemView="quiz.itemView.AnswerPercent"
                  emptyPage="hide"
                  gridLayout="Friend - Small List"
                  itemLayout="Quiz - Answer Played List"
                />
              ) : (
                <IndividualItem
                  item={item}
                  userId={value}
                  questions={questions}
                />
              )}
            </WrapContentStyled>
          </ScrollContainer>
        </DialogContent>
      </Box>
    </Dialog>
  );
}
