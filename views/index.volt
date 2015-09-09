<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />

		<title>{{ setup.title }}</title>

		<meta http-equiv="description" content="">
		<link rel="shortcut icon" type="image/x-icon" href="{{ url('public/favicon.ico') }}">

		<script>
			var url_base = '{{ url('') }}';
		</script>

	</head>
	<body>


		{{ content() }}


	</body>
</html>