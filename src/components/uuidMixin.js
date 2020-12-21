export const uuidMixin = {
  methods: {
    // v4 uuid https://gist.github.com/jed/982883
    genUuid() {
      let b = a =>
        a
          ? (a ^ (crypto.getRandomValues(new Uint8Array(1))[0] % 16 >> (a / 4))).toString(16)
          : ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, b);
      return b();
    }
  }
}