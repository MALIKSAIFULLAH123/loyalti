const mentionReg =
  /\[(user|page|group)=(\d+)\]([^[]+)\[\/(?:user|page|group)\]/gm;

const transform = value => {
  return value.replace(mentionReg, (match, p1, p2, p3) => {
    return `<a href="@mention/${
      p1 || 'user'
    }/${p2}"  data-lexical-mention="true">${p3}</a>`;
  });
};

export default transform;
