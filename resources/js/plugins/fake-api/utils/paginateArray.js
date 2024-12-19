export const paginateArray = (array, perPage, page) => {
    if (perPage < 0) {
      return array.slice((page - 1) * array.length, page * array.length);
    }
    return array.slice((page - 1) * perPage, page * perPage);
};
