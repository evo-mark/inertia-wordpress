import { Head, usePage } from "@inertiajs/react";
import { useMemo } from "react";

const TheSeoFramework = () => {
  const { seo } = usePage().props;
  const schema = useMemo(() => {
    return JSON.stringify({
      "@context": "https://schema.org",
      "@graph": seo.schema,
    });
  }, [seo]);
  return (
    <Head title={seo.title}>
      {seo.links.map((link) => {
        return <link head-key={link.href} key={link.href} {...link} />;
      })}
      {seo.meta.map((metaItem) => {
        const key = (metaItem.name ?? metaItem.property).replace(":", "_");
        return <meta key={key} head-key={key} {...metaItem} />;
      })}
      <script head-key="schema" type="application/ld+json">
        {schema}
      </script>
    </Head>
  );
};

export { TheSeoFramework };
