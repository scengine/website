<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<generator>BSE</generator>
		<title>{title}</title>
		<description>{description}</description>
		<atom:link rel="self" type="application/rss+xml" href="{self_url}" />
		<link>{site_url}</link>
		<language>{language}</language>
		<pubDate>{date}</pubDate>
		<lastBuildDate>{date}</lastBuildDate>
		<image>
			<title>{title}</title>
			<url>{icon}</url>
			<link>{site_url}</link>
		</image>
{foreach item in items}
		<item>
			<title>{item["title"]}</title>
			<link>{item["alternate_url"]}</link>
			<description>
				{item["content"]}
			</description>
			<pubDate>{item["date"]}</pubDate>
			<guid isPermaLink="false">{item["id"]}</guid>
		</item>
{end}
	</channel>
</rss>
