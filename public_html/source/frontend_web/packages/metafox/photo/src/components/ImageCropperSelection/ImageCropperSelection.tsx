import React from 'react';
import clsx from 'clsx';
import useStyles from './ImageCropperSelection.styles';

function CropperImageSelection({
  onMounted,
  onMouseDown,
  onTouchStart, 
  style
}, ref: any) {
  const classes = useStyles();

  React.useEffect(() => {
    if (onMounted)
      onMounted();
  }, []);

  return (
    <div
      onMouseDown={onMouseDown}
      onTouchStart={onTouchStart}
      className={classes.root}
      style={style}
      ref={ref}>
      <div className={clsx(classes.dragBar, classes['ordN'])} data-ord="n" />
      <div className={clsx(classes.dragBar, classes['ordE'])} data-ord="e" />
      <div className={clsx(classes.dragBar, classes['ordS'])} data-ord="s" />
      <div className={clsx(classes.dragBar, classes['ordW'])} data-ord="w" />

      <div className={clsx(classes.dragHandle, classes['ordNW'])} data-ord="nw" />
      <div className={clsx(classes.dragHandle, classes['ordN'])} data-ord="n" />
      <div className={clsx(classes.dragHandle, classes['ordNE'])} data-ord="ne" />
      <div className={clsx(classes.dragHandle, classes['ordE'])} data-ord="e"/>
      <div className={clsx(classes.dragHandle, classes['ordSE'])} data-ord="se"/>
      <div className={clsx(classes.dragHandle, classes['ordS'])} data-ord="s" />
      <div className={clsx(classes.dragHandle, classes['ordSW'])} data-ord="sw" />
      <div className={clsx(classes.dragHandle, classes['ordW'])} data-ord="w" />
    </div>);  
}

export default React.forwardRef(CropperImageSelection);

