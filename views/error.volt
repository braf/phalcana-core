<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />

		<title>{{ title }}</title>

		<meta http-equiv="description" content="">
		<link rel="shortcut icon" type="image/x-icon" href="{{ url('public/favicon.ico') }}">

		<style>
			body,html {
				height: 100%;
			}
			body {
				font-family: Arial, Helvetica, sans-serif;
				background-color: #CCC;
				color: #333;
				margin: 0;
			}
			.row {
				margin: 0 auto;
				padding: 0 1.5em;
				max-width: 640epx;
				display: table;
				height:100%;
			}
			.inner {
				display: table-cell;
				vertical-align: middle;
			}
			h1 {
				font-size: 6em;
				margin: 0 0 ;
				padding-right: 0.3em;
				display: inline-block;
				vertical-align: middle;
			}
			h2 {
				margin: 0;
				vertical-align: middle;
				display: inline-block;
				font-size: 2em;
			}
			p {
				margin-bottom: 0.5em;
			}
			ul {
				padding: 0;
				margin-top: 0.5em;
				margin-bottom: 2em;
				position: relative;
				list-style: none;

			}
			li {
				padding-left: 1.5em;
				position: relative;
			}
			li:before {
				content: "-";
				position: absolute;
				left:0;
				width: 1.5em;
				text-align: center;
			}
		</style>

	</head>
	<body>

		<div class="row">
			<div class="inner">
				{{ content() }}
			</div>
		</div>



		{% if config.google_analytics is defined %}
			<script>
				(function (i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function (){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

				ga('create', '{{ config.google_analytics }}', 'auto');
				ga('send', 'pageview');



			</script>
		{% endif %}

	</body>
</html>
