<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<generator>BSE</generator>
	<icon>{icon}</icon>
	<title>{title}</title>
	<link rel="self" href="{self_url}" />
	<link rel="alternate" href="{alternate_url}" />
	<updated>{date}</updated>
	<id>{id}</id>
	<author>
		<name>{author}</name>
	</author>
{foreach item in items}
	<entry>
		<title xml:lang="{item["lang"]}">{item["title"]}</title>
		<content type="html" xml:lang="{item["lang"]}">
			{item["content"]}
		</content>
		<updated>{item["date"]}</updated>
		<link rel="alternate" href="{item["alternate_url"]}" />
		<id>{item["id"]}</id>
	</entry>
{end}
</feed>
