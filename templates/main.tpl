<!DOCTYPE html>
<html>
	<head>
		:head_tag
		<script>csrf_token = ':csrf_token';</script>
	</head>
	<body onload = "mainStart()">
		<div id = "divHeaderMain">
			:header
		</div>
		<div id = "divContentMain">
			<div id = "divContentNavbar"></div>
			<div id = "divContentTitleContainer">
				<span id = "spanContentTitleText" class = "vaultra-title"></span>
				<span id = "spanContentSubtitleText" class = "vaultra-subtitle"></span>
			</div>
			<div id = "divContentContent">
				:content
			</div>
			<div id = "divContentLog">
				<div id = "divContentLogTitle">
					<span id = "spanContentLogTitleText" class = "vertical-center">RECENT ATTACKS</span>
				</div>
				<div id = "divContentLogText"></div>
			</div>
			<div id = "divContentStatistics">
			</div>
		</div>
	</body>
</html>