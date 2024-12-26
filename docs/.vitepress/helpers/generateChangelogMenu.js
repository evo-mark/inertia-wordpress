import { readdirSync, readFileSync, writeFileSync } from "node:fs";
import { resolve, join } from "node:path";

export function generateChangelogMenu() {
  const dir = resolve("./docs/changelogs");
  const files = readdirSync(dir)
    .filter((file) => file.endsWith(".md") && !file.endsWith("next.md"))
    .map((file) => {
      const splitIndex = file.indexOf("-");
      const [version, date] = [
        file.slice(0, splitIndex),
        file.slice(splitIndex + 1),
      ];
      const uploadedDate = new Date(
        date.replace(".md", ""),
      ).toLocaleDateString();

      const fullPath = join(dir, file);
      const content = readFileSync(fullPath, "utf-8");

      const injected = `# Version ${version.replace("v", "")}

> Uploaded on ${uploadedDate}\n\n`;
      writeFileSync(fullPath, injected + content);

      return {
        text: version.replace("v", ""),
        link: "/changelogs/" + file,
      };
    })
    .sort((a, b) => b.text.localeCompare(a.text, undefined, { numeric: true }))
    .slice(0, 10);
  return {
    text: "Current Version: " + files[0].text,
    items: files,
  };
}
