<ul>
	{foreach item in items}
		<li>
			<a href="{item["link"]}"
			   title="By {item["author"]} on {item["date"]}"
			>{item["title"]}</a>
		</li>
	{end}
</ul>
