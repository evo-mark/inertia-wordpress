import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import eslintConfigPrettier from "eslint-config-prettier";
import AutoImportJson from "./eslint-auto-import.json" assert { type: "json" };

export default [
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    AutoImportJson,
    {
        env: {
            node: true,
        },
        rules: {
            "vue/multi-word-component-names": "off",
            "vue/valid-v-slot": [
                "error",
                {
                    allowModifiers: true,
                },
            ],
            "vue/no-v-html": "off",
        },
        globals: {
            defineModel: true,
        },
        parserOptions: {
            ecmaVersion: 2020,
        },
    },
    eslintConfigPrettier,
];
