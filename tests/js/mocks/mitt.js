// Mock implementation of mitt for testing
export default function mitt() {
  return {
    on: jest.fn(),
    off: jest.fn(),
    emit: jest.fn()
  };
}
