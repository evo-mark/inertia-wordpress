import { cpSync, rmSync } from "node:fs";
import { resolve, basename } from "node:path";

export const copyChangelogs = () => {
  rmSync(resolve("./docs/changelogs"), {
    force: true,
    recursive: true,
  });
  cpSync(resolve("./changelog"), resolve("./docs/changelogs"), {
    force: true,
    recursive: true,
    filter: (source) => {
      const filename = basename(source);
      return filename !== "next.md";
    },
  });
};
