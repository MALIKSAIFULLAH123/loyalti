/**
 * @type: dialog
 * name: dialog.DialogCustomListPrivacy
 */
import { useGlobal, useResourceAction } from '@metafox/framework';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import CheckBoxIcon from '@mui/icons-material/CheckBox';
import CheckBoxOutlineBlankIcon from '@mui/icons-material/CheckBoxOutlineBlank';
import { isArray, isEmpty, range } from 'lodash';
import {
  Button,
  Checkbox as MuiCheckbox,
  FormControlLabel,
  FormGroup,
  Typography
} from '@mui/material';
import React from 'react';
import produce from 'immer';
import useStyles from './DialogCustomListPrivacy.styles';
import FriendListLoadingSkeleton from './FriendListLoadingSkeleton';
import { ScrollContainer } from '@metafox/layout';
import { compactData } from '@metafox/utils';

export type CustomListPrivacyProps = {
  open?: boolean;
  onCloseDialog?: () => void;
};

const toArrayIsSelected = array => {
  const newArray = [];

  array.forEach(item => {
    if (item?.is_selected) newArray.push(item?.id);
  });

  return newArray;
};

export default function DialogCustomListPrivacy({ value, feed }) {
  const classes = useStyles();
  const { useDialog, i18n, useFetchItems, dispatch, getAcl } = useGlobal();
  const { dialogProps, setDialogValue, closeDialog } = useDialog();

  const config = useResourceAction(
    'core',
    'privacy',
    'getCustomPrivacyOptions'
  );

  const canCreateNewList = getAcl('friend.friend_list.create');

  const [customListPrivacy, loading] = useFetchItems({
    dataSource: {
      apiUrl: config.apiUrl,
      apiParams: compactData(config.apiParams, feed)
    },
    normalize: false
  });

  const [customList, setCustomList] = React.useState(customListPrivacy);
  const [values, setValues] = React.useState<number[]>(
    Array.isArray(value) ? value : []
  );

  React.useEffect(() => {
    setCustomList(customListPrivacy);

    if (!isEmpty(customListPrivacy) && !isArray(value))
      setValues(toArrayIsSelected(customListPrivacy));
  }, [customListPrivacy, value]);

  const handleSubmit = () => {
    closeDialog();
    setDialogValue(values);
  };

  const handleChange = (v: number): void => {
    setValues(prev => {
      // eslint-disable-next-line eqeqeq
      if (prev.find(x => x == v)) {
        // eslint-disable-next-line eqeqeq
        return prev.filter(x => x != v);
      } else {
        return [...prev, v];
      }
    });
  };

  const handleCreateNewCustomList = React.useCallback(() => {
    dispatch({
      type: 'friend/addNewListWhenCustomPrivacy',
      meta: {
        onSuccess: response => {
          setValues(prev =>
            produce(prev, draft => {
              draft.push(response.id);
            })
          );
          setCustomList(prev =>
            produce(prev, draft => {
              draft.push(response);
            })
          );
        }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: 'custom_privacy' })}</DialogTitle>
      <DialogContent>
        <div className={classes.shareWith}>
          {i18n.formatMessage({ id: 'share_with' })}
        </div>
        <ScrollContainer autoHide autoHeight autoHeightMax={200}>
          {loading ? (
            range(1, 5).map(index => (
              <FriendListLoadingSkeleton key={index.toString()} />
            ))
          ) : (
            <FormGroup>
              {customList?.length === 0 ? (
                <Typography
                  paragraph
                  variant="body1"
                  margin="0"
                  color="textSecondary"
                >
                  {i18n.formatMessage({ id: 'there_are_no_friend_list' })}
                </Typography>
              ) : (
                customList?.map((item, index) => (
                  <FormControlLabel
                    checked={-1 < values.indexOf(item.id)}
                    key={index.toString()}
                    value={item.id}
                    label={item.title}
                    onChange={() => handleChange(item.id)}
                    control={
                      <MuiCheckbox
                        value={item}
                        icon={<CheckBoxOutlineBlankIcon fontSize="small" />}
                        checkedIcon={<CheckBoxIcon fontSize="small" />}
                        color="primary"
                      />
                    }
                  />
                ))
              )}
            </FormGroup>
          )}
        </ScrollContainer>
        <div className={classes.linkContainer}>
          {canCreateNewList && (
            <>
              <Button
                variant="link"
                size="small"
                color="primary"
                onClick={handleCreateNewCustomList}
              >
                {i18n.formatMessage({ id: 'create_new_custom_list' })}
              </Button>
              &nbsp;
              <span> {i18n.formatMessage({ id: 'or' })} </span>
              &nbsp;
            </>
          )}
          <Button variant="link" size="small" href="/friend" color="primary">
            {i18n.formatMessage({ id: 'manage_lists' })}
          </Button>
        </div>
      </DialogContent>
      <DialogActions>
        <Button
          variant="contained"
          onClick={handleSubmit}
          size="small"
          color="primary"
          disabled={0 === values.length}
        >
          {i18n.formatMessage({ id: 'save' })}
        </Button>
        <Button variant="outlined" onClick={closeDialog} size="small">
          {i18n.formatMessage({ id: 'cancel' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
