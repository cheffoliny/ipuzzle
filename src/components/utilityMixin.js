export const utilityMixin = {
  methods: {
    //https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/reduce
    groupBy(objectArray, property) {
      return objectArray.reduce((acc, obj) => {
        let key = obj[property];
        if (!acc[key]) {
          acc[key] = [];
        }
        acc[key].push(obj);
        return acc;
      }, {});
    },
    arrSortByPropName(arr, name) {
      return arr.sort((a, b) => {
        let textA = a[name].toUpperCase();
        let textB = b[name].toUpperCase();
        return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
      })
    },
    filterKey(e) {
      const key = e.key;
      // If is '.' key, stop it
      if (key === ".") return e.preventDefault();
      // If is 'e' key, stop it
      if (key === "e") return e.preventDefault();
    },
    // prevent copy + paste invalid character
    filterInput(e) {
      e.target.value = e.target.value.replace(/[^0-9]+/g, "");
    }
  },
  filters: {
    price(value) {
      if (value === "") return;
      return parseFloat(value).toFixed(2);
    }
  }
}