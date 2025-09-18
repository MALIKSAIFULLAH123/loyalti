/**
 * @type: itemView
 * name: group.itemView.membershipQuestionCard
 * chunkName: group
 */

import { connectItemView, useGlobal } from '@metafox/framework';
import { ItemText, ItemTitle, ItemView } from '@metafox/ui';
import { CardActions, Checkbox, Divider, Grid, Radio } from '@mui/material';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import { isEmpty } from 'lodash';
import * as React from 'react';
import { TypeQuestion } from './types';

interface Props {
  item?: { question?: any; type: any; options: Array<any>; _identity: string };
  index: number;
}

const CheckboxQuestion = ({ value }: { value: string }) => {
  return (
    <Grid container alignItems="flex-start" wrap="nowrap">
      <Checkbox disabled checked />
      <Typography variant="body2" sx={{ pt: 1.5, pb: 1 }}>
        {value}
      </Typography>
    </Grid>
  );
};

const SelectQuestion = ({ value }: { value: string }) => {
  return (
    <Grid container alignItems="flex-start" wrap="nowrap">
      <Radio disabled />
      <Typography variant="body2" sx={{ pt: 1.5, pb: 1 }}>
        {value}
      </Typography>
    </Grid>
  );
};

const MembershipQuestionCard = ({ item, wrapAs, wrapProps }: Props) => {
  const { i18n, dispatch } = useGlobal();

  if (isEmpty(item)) return null;

  const { question, type, options } = item;

  const answerRender = (value: any) => {
    const { title } = value;

    if (type === TypeQuestion.CheckBox)
      return <CheckboxQuestion value={title} />;

    if (type === TypeQuestion.Select) return <SelectQuestion value={title} />;
  };

  const handleEditQuestion = () => {
    dispatch({
      type: 'updateMembershipQuestion',
      payload: {
        initValues: item,
        identity: item._identity
      }
    });
  };

  const handleRemove = () => {
    dispatch({
      type: 'group/removeMembershipQuestion',
      payload: { identity: item._identity }
    });
  };

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps}>
      <ItemText>
        <ItemTitle>{question}</ItemTitle>
      </ItemText>
      {type !== TypeQuestion.FreeAnswer ? (
        options.map(opt => (
          <React.Fragment key={opt.id}>
            {answerRender(opt)} <Divider />
          </React.Fragment>
        ))
      ) : (
        <Divider />
      )}
      <CardActions>
        <Button
          onClick={handleEditQuestion}
          sx={{ width: '100px' }}
          variant="contained"
          size="small"
        >
          {i18n.formatMessage({ id: 'edit' })}
        </Button>
        <Button
          onClick={handleRemove}
          sx={{ width: '100px' }}
          variant="outlined"
          size="small"
        >
          {i18n.formatMessage({ id: 'delete' })}
        </Button>
      </CardActions>
    </ItemView>
  );
};

export default connectItemView(MembershipQuestionCard, () => {});
