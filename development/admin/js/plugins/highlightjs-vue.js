export default function (hljs) {
	return {
		name: "VUE",
		aliases: ["vue"],
		subLanguage: "html",
		contains: [
			hljs.COMMENT("<!--", "-->", { relevance: 10 }),
			{
				begin: /<template>/gm,
				end: /<\/template>/gm,
				subLanguage: "html",
				excludeBegin: true,
				excludeEnd: true,
			},
			{
				begin: /<script(?!.*lang=["']ts["'])>/gm,
				end: /<\/script>/gm,
				subLanguage: "js",
				excludeBegin: true,
				excludeEnd: true,
			},
			{
				begin: /<script\s+lang=["']ts["']>/gm,
				end: /<\/script>/gm,
				subLanguage: "ts",
				excludeBegin: true,
				excludeEnd: true,
			},
			{
				begin: /^(\s*)(<style.*>)/gm,
				end: /^(\s*)(<\/style>)/gm,
				subLanguage: "css",
				excludeBegin: true,
				excludeEnd: true,
			},
			{
				begin: /\{/,
				end: /\}/,
				subLanguage: "javascript",
				contains: [
					hljs.COMMENT(/\/\*/, /\*\//),
					{ begin: /\{/, end: /\}/, skip: true },
					{
						begin: /[#@/][a-zA-Z_][\w-]*/,
						className: "keyword",
						relevance: 10,
					},
				],
			},
		],
	};
}
