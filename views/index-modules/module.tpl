<div class="module {extra_classes}">
	{if title}
		<h3>{title}
			{if feed}
				<span class="fright">
					<a href="{feed}" title="Subscribe to the Feed">
						<img alt="Atom Feed" src="styles/{STYLE}/feed.png"/>
					</a>
				</span>
			{end}
		</h3>
	{end}
	<div class="data">
		{data}
		{if links}
			<div class="links">
				<ul>
				{foreach href,title in links}
					<li><a href="{href}">{title}</a></li>
				{end}
				</ul>
			</div>
		{end}
	</div>
</div>
