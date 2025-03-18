module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'json', 'vue'],
  transform: {
    '^.+\\.js$': 'babel-jest',
    '^.+\\.vue$': '@vue/vue3-jest'
  },
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
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
    '/node_modules/(?!vue-router|@babel|vuex)'
  ]
}
