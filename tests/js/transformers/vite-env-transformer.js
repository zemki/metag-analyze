// A custom Jest transformer to handle import.meta.env
const { default: babelJest } = require('babel-jest');

module.exports = babelJest.createTransformer({
  plugins: [
    function() {
      return {
        visitor: {
          MetaProperty(path) {
            // Replace import.meta.env with a global variable
            if (
              path.node.meta.name === 'import' &&
              path.node.property.name === 'meta'
            ) {
              path.replaceWithSourceString('({ env: { VITE_ENV_MODE: "development" } })');
            }
          }
        }
      };
    }
  ]
});
