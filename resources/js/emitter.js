import mitt from "mitt";

// The app-wide event bus. Components import it from here, never from the
// entry file app.js: on Windows, Rollup sees the entry and a component's
// "../app.js" import as two modules, bundles app.js twice and mounts Vue twice.
export const emitter = mitt();
