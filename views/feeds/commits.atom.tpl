<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<generator>BSE</generator>
	<icon>{icon}</icon>
	<title>{title}</title>
	<link rel="self" href="{self_url}" />
	<link rel="alternate" href="{alternate_url}" />
	<updated>{date}</updated>
	<id>{id}</id>
{foreach entry in entries}
	<entry>
		<title xml:lang="{entry["lang"]}">{entry["title"]}</title>
		<content type="html" xml:lang="{entry["lang"]}">
			{entry["content"]}
		</content>
		<updated>{entry["date"]}</updated>
		<link rel="alternate" href="{entry["alternate_url"]}" />
		<id>{entry["id"]}</id>
		<author>
			<name>{entry["author"]}</name>
		</author>
	</entry>
{end}
</feed>
