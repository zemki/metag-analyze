// Mock app.js for testing
import mitt from 'mitt';

// Create a mocked emitter for testing
export const emitter = {
  on: jest.fn(),
  off: jest.fn(),
  emit: jest.fn()
};

// Mock the env variables
export const viteEnv = {
  VITE_ENV_MODE: 'development'
};
