import { Dialog, DialogTitle } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { TruncateText } from '@metafox/ui';
import { styled, Tab, Tabs } from '@mui/material';
import React, { useMemo, useState } from 'react';
import useStyles from './styles';
import { camelCase } from 'lodash';

const name = 'PeopleWhoVoted';
const tabsStyle = {
  minHeight: 32
};

const TabStyled = styled(Tab, { name })(({ theme }) => ({
  justifyContent: 'space-between',
  maxWidth: '100%',
  padding: `${theme.spacing(0.5, 1)} !important`,
  height: `${theme.spacing(3.5)} !important`,
  marginRight: '0px !important',
  marginBottom: theme.spacing(0.5)
}));
const TruncateTextStyled = styled(TruncateText, { name })(({ theme }) => ({
  maxWidth: '95%'
}));

export default function PeopleWhoVoted(props) {
  const { listAnswers: tabs } = props;
  const classes = useStyles();
  const [value, setValue] = useState<number>(tabs[0].id);
  const { useDialog, i18n, ListView } = useGlobal();
  const { dialogProps } = useDialog();
  const scrollRef = React.useRef<HTMLDivElement>();

  const dataSource = useMemo(
    () => ({
      apiUrl: '/poll-result',
      apiParams: {
        answer_id: value
      }
    }),
    [value]
  );
  const pagingId = `pollAnswerResult/${value}`;

  const handleChange = (event: React.ChangeEvent<{}>, newValue: string) => {
    setValue(parseInt(newValue, 10));
  };

  React.useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTo({ top: 0 });
    }
  }, [value]);

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>
        {i18n.formatMessage({ id: 'people_who_voted' })}
      </DialogTitle>
      <div>
        <Tabs
          value={value}
          onChange={handleChange}
          style={tabsStyle}
          className={classes.customTabs}
        >
          {tabs.map((tab, index) => (
            <TabStyled
              key={tab.id}
              disableRipple
              data-testid={camelCase(`button_tab ${index + 1}`)}
              label={
                tab.answer ? (
                  <>
                    <TruncateTextStyled lines={1}>
                      {tab.answer}
                    </TruncateTextStyled>
                    <span className={classes.reactNumber}>
                      {tab.total_votes}
                    </span>
                  </>
                ) : undefined
              }
              value={tab.id}
              aria-label={tab.title}
            />
          ))}
        </Tabs>
        <div className={classes.dialogContent}>
          <ScrollContainer autoHide autoHeightMax="auto" ref={scrollRef}>
            <ListView
              dataSource={dataSource}
              pagingId={pagingId}
              canLoadMore
              gridLayout="Poll - People Who Voted"
              itemLayout="Poll - People Who Voted"
              itemView="poll.itemView.peopleWhoVoted"
              emptyPage="core.block.no_content"
              emptyPageProps={{
                title: 'no_votes',
                contentStyle: {
                  sx: {
                    pt: 2
                  }
                }
              }}
            />
          </ScrollContainer>
        </div>
      </div>
    </Dialog>
  );
}
