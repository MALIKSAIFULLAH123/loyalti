import React from 'react';
import useStyles from './styles';

export default function ItemView({
  item,
  identity,
  wrapAs: WrapAs,
  wrapProps
}) {
  const classes = useStyles();

  return (
    <WrapAs {...wrapProps} testid={`${item.resource_name}`} data-eid={identity}>
      <div className={classes.root}>Forum Item View</div>
    </WrapAs>
  );
}

ItemView.displayName = 'ForumAnnouncementItem(MainCard)';
