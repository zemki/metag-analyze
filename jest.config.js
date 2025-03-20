module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'json', 'vue'],
  transform: {
    '^.+\\.js$': '<rootDir>/tests/js/transformers/vite-env-transformer.js',
    '^.+\\.vue$': '@vue/vue3-jest'
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    // Mock app.js imports to avoid circular dependencies
    '^../app$': '<rootDir>/tests/js/mocks/app.js',
    '^./app$': '<rootDir>/tests/js/mocks/app.js',
    // Mock mitt
    'mitt': '<rootDir>/tests/js/mocks/mitt.js'
  },
  testMatch: [
    '**/tests/js/**/*.spec.js',
    '**/resources/js/**/*.spec.js'
  ],
  setupFilesAfterEnv: [
    '<rootDir>/tests/js/setup.js'
  ],
  testEnvironmentOptions: {
    customExportConditions: ['node', 'node-addons'],
  },
  transformIgnorePatterns: [
    '/node_modules/(?!vue-router|@babel|vuex|mitt)'
  ]
}
